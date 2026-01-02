<?php

namespace Sylphian\Verify\XF\Pub\Controller;

use Sylphian\Verify\Entity\Account;
use XF\Db\DuplicateKeyException;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\PrintableException;

class AccountController extends XFCP_AccountController
{
	public function actionMinecraft(): Redirect|View|Error
	{
		$visitor = \XF::visitor();

		$accounts = $this->finder('Sylphian\Verify:Account')
			->where('user_id', $visitor->user_id)
			->where('provider', 'minecraft')
			->order('add_date')
			->fetch();

		if ($this->isPost())
		{
			if ($accounts->count() >= 3)
			{
				return $this->error(\XF::phrase('sylphian_verify_too_many_accounts'));
			}

			$username = $this->filter('username', 'str');

			if (!$username)
			{
				return $this->error(\XF::phrase('sylphian_verify_please_fill_all_fields'));
			}

			try
			{
				$client = $this->app()->http()->client();
				$usernameEncoded = urlencode($username);
				$response = $client->get("https://api.mojang.com/users/profiles/minecraft/{$usernameEncoded}", [
					'http_errors' => false,
				]);
				$contents = $response->getBody()->getContents();
				$data = json_decode($contents, true);

				if ($response->getStatusCode() == 204 || $response->getStatusCode() == 404 || !$data || !isset($data['id']))
				{
					return $this->error(\XF::phrase('sylphian_verify_minecraft_account_not_found', ['username' => $username]));
				}

				$uuid = $data['id'];
				$username = $data['name'];

				$uuid = substr($uuid, 0, 8) . '-' .
					substr($uuid, 8, 4) . '-' .
					substr($uuid, 12, 4) . '-' .
					substr($uuid, 16, 4) . '-' .
					substr($uuid, 20);
			}
			catch (\Exception $e)
			{
				return $this->error(\XF::phrase('sylphian_verify_mojang_api_error'));
			}

			$existing = $this->finder('Sylphian\Verify:Account')
				->where('provider', 'minecraft')
				->where('provider_key', $uuid)
				->fetchOne();

			if ($existing)
			{
				return $this->error(\XF::phrase('sylphian_verify_account_already_linked'));
			}

			/* @var Account $account */
			$account = $this->em()->create('Sylphian\Verify:Account');
			$account->user_id = $visitor->user_id;
			$account->provider = 'minecraft';
			$account->provider_key = $uuid;
			$account->username = $username;

			try
			{
				$account->save();
			}
			catch (DuplicateKeyException $e)
			{
				return $this->error(\XF::phrase('sylphian_verify_account_already_linked'));
			}
			catch (PrintableException $e)
			{
				return $this->error($e->getMessage());
			}

			return $this->redirect($this->buildLink('account/minecraft'));
		}

		$viewParams = [
			'accounts' => $accounts,
		];

		$view = $this->view(
			'Sylphian\Verify:Account\Minecraft',
			'sylphian_verify_account_minecraft',
			$viewParams
		);

		return $this->addAccountWrapperParams($view, 'minecraft');
	}

	public function actionMinecraftDelete(ParameterBag $params): Redirect|View|AbstractReply
	{
		$visitor = \XF::visitor();
		$accountId = $this->filter('account_id', 'uint');

		$account = $this->assertMinecraftAccountExists($accountId);

		if ($account->user_id != $visitor->user_id)
		{
			return $this->noPermission();
		}

		if ($this->isPost())
		{
			$account->delete();
			return $this->redirect($this->buildLink('account/minecraft'));
		}

		$viewParams = [
			'account' => $account,
		];
		return $this->view('Sylphian\Verify:Account\MinecraftDelete', 'sylphian_verify_account_minecraft_delete', $viewParams);
	}

	protected function assertMinecraftAccountExists($id, $with = null, $phraseKey = null): Account
	{
		/** @var Account $account */
		$account = $this->assertRecordExists('Sylphian\Verify:Account', $id, $with, $phraseKey);
		return $account;
	}
}
