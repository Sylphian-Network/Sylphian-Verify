<?php

namespace Sylphian\Verify\Api\Controller;

use Psr\Cache\InvalidArgumentException;
use Sylphian\Library\Logger\Logger;
use Sylphian\Verify\Entity\Account;
use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;

class Verification extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params): void
	{
		$this->assertApiScopeByRequestMethod('sylphian_verify');
	}

	public function allowUnauthenticatedRequest($action): bool
	{
		return false;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function actionGetMinecraft(): AbstractReply
	{
		$uuid = $this->filter('uuid', 'str');
		if (!$uuid)
		{
			return $this->apiResult([
				'allowed' => false,
				'reason' => 'Please provide a UUID',
			]);
		}

		$uuid = $this->normaliseMinecraftUuid($uuid);
		if (!$uuid)
		{
			return $this->apiResult([
				'allowed' => false,
				'reason' => 'Invalid UUID format',
			]);
		}

		/** @var Account $account */
		$account = $this->finder('Sylphian\Verify:Account')
			->where('provider', 'minecraft')
			->where('provider_key', $uuid)
			->with('User', true)
			->fetchOne();

		if (!$account || !$account->User)
		{
			return $this->apiResult([
				'allowed' => false,
				'reason' => 'UUID not linked to any forum account',
			]);
		}

		if ($account->confirmed)
		{
			return $this->apiResult([
				'allowed' => true,
				'forum_username' => $account->User->username,
				'minecraft_username' => $account->username,
				'link_date' => $account->add_date,
				'confirmed_date' => $account->confirmed_date,
			]);
		}

		$cache = $this->app()->cache('', true, false);
		$cacheKey = "sylphian_verify_passcode_{$account->account_id}";
		$item = $cache?->getItem($cacheKey);
		$logger = Logger::withAddonId('Sylphian/Verify');

		if ($item && $item->isHit())
		{
			$passcode = $item->get();
			$logger->debug("Retrieved existing passcode {passcode} for account ID {account_id}", [
				'passcode' => $passcode,
				'account_id' => $account->account_id,
			]);
		}
		else
		{
			$passcode = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

			if ($cache && $item)
			{
				$item->set($passcode);
				$item->expiresAfter(600);
				$cache->save($item);

				$logger->info("Generated new passcode {passcode} for account ID {account_id}", [
					'passcode' => $passcode,
					'account_id' => $account->account_id,
				]);
			}
		}

		return $this->apiResult([
			'allowed' => false,
			'reason' => 'Account not confirmed',
			'passcode' => $passcode,
			'forum_username' => $account->User->username,
            'minecraft_username' => $account->username,
            'link_date' => $account->add_date,
		]);
	}

	//TODO: This will eventually point to actual documentation if I never use it for anything else.
	public function actionGet(): AbstractReply
	{
		return $this->apiResult([
			'minecraft_usage' => 'Use /api/verify/minecraft?uuid=...',
		]);
	}

	protected function normaliseMinecraftUuid(string $uuid): string
	{
		$uuid = str_replace('-', '', $uuid);

		if (strlen($uuid) === 32 && ctype_xdigit($uuid))
		{
			return substr($uuid, 0, 8) . '-' .
				substr($uuid, 8, 4) . '-' .
				substr($uuid, 12, 4) . '-' .
				substr($uuid, 16, 4) . '-' .
				substr($uuid, 20);
		}

		return '';
	}
}
