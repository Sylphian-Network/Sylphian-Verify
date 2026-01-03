<?php

namespace Sylphian\Verify\Api\Controller;

use Sylphian\Verify\Entity\Account;
use XF\Api\Controller\AbstractController;
use XF\Mvc\Reply\AbstractReply;

class Verification extends AbstractController
{
	public function allowUnauthenticatedRequest($action): bool
	{
		return false;
	}

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

		if ($account && $account->User)
		{
			return $this->apiResult([
				'allowed' => true,
				'forum_username' => $account->User->username,
				'minecraft_username' => $account->username,
				'link_date' => $account->add_date,
			]);
		}

		return $this->apiResult([
			'allowed' => false,
			'reason' => 'UUID not linked to any forum account',
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
