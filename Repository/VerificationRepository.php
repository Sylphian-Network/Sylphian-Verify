<?php

namespace Sylphian\Verify\Repository;

use Sylphian\Verify\Entity\Account;
use XF\Mvc\Entity\AbstractCollection;
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
	 * @param array $uuids
	 * @return AbstractCollection
	 */
	public function getAccountsByMinecraftUuids(array $uuids): AbstractCollection
	{
		return $this->finder('Sylphian\Verify:Account')
			->where('provider', 'minecraft')
			->where('provider_key', $uuids)
			->with('User', true)
			->fetch();
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
		return $this->getPasscodeDetails($account)['passcode'];
	}

	/**
	 * @param Account $account
	 * @return array
	 */
	public function getPasscodeDetails(Account $account): array
	{
		$cache = $this->app()->cache();
		$cacheKey = "sylphian_verify_passcode_{$account->account_id}";
		$data = $cache ? $cache->fetch($cacheKey) : false;

		if (is_array($data))
		{
			$passcode = $data['passcode'] ?? '';
			$expiry = $data['expiry'] ?? 0;
		}
		else
		{
			$passcode = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
			$expiryTime = $this->options()->sylphian_verify_passcode_expiry ?: 600;
			$expiry = \XF::$time + $expiryTime;
			if ($cache)
			{
				$cache->save($cacheKey, [
					'passcode' => $passcode,
					'expiry' => $expiry,
				], $expiryTime);
			}
		}

		return [
			'passcode' => $passcode,
			'expires' => $expiry,
			'remaining_seconds' => $expiry ? max(0, $expiry - \XF::$time) : 0,
		];
	}

	/**
	 * @param Account $account
	 * @return array
	 */
	public function getBruteForceDetails(Account $account): array
	{
		$cache = $this->app()->cache();
		if (!$cache)
		{
			return ['is_blocked' => false, 'attempts' => 0];
		}

		$failedKey = "sylphian_verify_failed_attempts_{$account->account_id}";
		$data = $cache->fetch($failedKey);

		$limit = $this->options()->sylphian_verify_failed_attempts_limit ?: 5;

		if (!is_array($data))
		{
			return [
				'is_blocked' => false,
				'attempts' => 0,
				'attempts_remaining' => $limit,
				'block_expires' => 0,
				'remaining_seconds' => 0,
			];
		}

		$attempts = $data['attempts'] ?? 0;
		$expiry = $data['expiry'] ?? 0;

		return [
			'is_blocked' => ($attempts >= $limit),
			'attempts' => $attempts,
			'attempts_remaining' => max(0, $limit - $attempts),
			'block_expires' => $expiry,
			'remaining_seconds' => $expiry ? max(0, $expiry - \XF::$time) : 0,
		];
	}

	/**
	 * @param Account $account
	 * @return void
	 */
	public function increaseFailedAttempts(Account $account): void
	{
		$cache = $this->app()->cache();
		if (!$cache)
		{
			return;
		}

		$failedKey = "sylphian_verify_failed_attempts_{$account->account_id}";
		$data = $cache->fetch($failedKey);

		$attempts = (($data['attempts'] ?? 0) ?: 0) + 1;
		$blockDuration = $this->options()->sylphian_verify_block_expiry ?: 3600;
		$expiry = ($data['expiry'] ?? 0) ?: (\XF::$time + $blockDuration);

		$cache->save($failedKey, [
			'attempts' => $attempts,
			'expiry' => $expiry,
		], max(0, $expiry - \XF::$time));
	}

	/**
	 * @param Account $account
	 * @return void
	 */
	public function resetFailedAttempts(Account $account): void
	{
		$cache = $this->app()->cache();
		$cache?->delete("sylphian_verify_failed_attempts_{$account->account_id}");
	}
}
