<?php

namespace Sylphian\Verify\Repository;

use Sylphian\Verify\Entity\Account;
use XF\Mvc\Entity\Repository;

class VerificationRepository extends Repository
{
	/**
	 * @param string $uuid
	 * @return Account|null
	 */
	public function getAccountByMinecraftUuid(string $uuid): ?Account
	{
		/** @var Account|null $account */
		$account = $this->finder('Sylphian\Verify:Account')
			->where('provider', 'minecraft')
			->where('provider_key', $uuid)
			->with('User', true)
			->fetchOne();

		return $account;
	}

	/**
	 * @param string $uuid
	 * @return string
	 */
	public function normaliseMinecraftUuid(string $uuid): string
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

	/**
	 * @param Account $account
	 * @return string
	 */
	public function getPasscode(Account $account): string
	{
		$cache = $this->app()->cache('', true, false);
		$cacheKey = "sylphian_verify_passcode_{$account->account_id}";
		$item = $cache?->getItem($cacheKey);

		if ($item && $item->isHit())
		{
			return $item->get();
		}

		$passcode = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
		if ($cache && $item)
		{
			$item->set($passcode);
			$item->expiresAfter(600);
			$cache->save($item);
		}
		return $passcode;
	}
}
