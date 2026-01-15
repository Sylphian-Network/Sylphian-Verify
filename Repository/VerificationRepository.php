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
		return $this->getPasscodeDetails($account)['passcode'];
	}

	/**
	 * @param Account $account
	 * @return array
	 */
	public function getPasscodeDetails(Account $account): array
	{
		$cache = $this->app()->cache('', true, false);
		$cacheKey = "sylphian_verify_passcode_{$account->account_id}";
		$item = $cache?->getItem($cacheKey);

		if ($item && $item->isHit())
		{
			$data = $item->get();
			$passcode = $data['passcode'] ?? '';
			$expiry = $data['expiry'] ?? 0;
		}
		else
		{
			$passcode = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
			$expiryTime = $this->options()->sylphian_verify_passcode_expiry ?: 600;
			$expiry = \XF::$time + $expiryTime;
			if ($cache && $item)
			{
				$item->set([
					'passcode' => $passcode,
					'expiry' => $expiry,
				]);
				$item->expiresAt(new \DateTime("@$expiry"));
				$cache->save($item);
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
		$cache = $this->app()->cache('', true, false);
		if (!$cache)
		{
			return ['is_blocked' => false, 'attempts' => 0];
		}

		$failedKey = "sylphian_verify_failed_attempts_{$account->account_id}";
		$item = $cache->getItem($failedKey);

		$limit = $this->options()->sylphian_verify_failed_attempts_limit ?: 5;

		if (!$item->isHit())
		{
			return [
				'is_blocked' => false,
				'attempts' => 0,
				'attempts_remaining' => $limit,
				'block_expires' => 0,
				'remaining_seconds' => 0,
			];
		}

		$data = $item->get();
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
		$cache = $this->app()->cache('', true, false);
		if (!$cache)
		{
			return;
		}

		$failedKey = "sylphian_verify_failed_attempts_{$account->account_id}";
		$item = $cache->getItem($failedKey);

		$data = $item->isHit() ? $item->get() : null;
		$attempts = ($data['attempts'] ?? 0) + 1;
		$blockDuration = $this->options()->sylphian_verify_block_expiry ?: 3600;
		$expiry = ($data['expiry'] ?? 0) ?: (\XF::$time + $blockDuration);

		$item->set([
			'attempts' => $attempts,
			'expiry' => $expiry,
		]);
		$item->expiresAt(new \DateTime("@$expiry"));
		$cache->save($item);
	}

	/**
	 * @param Account $account
	 * @return void
	 */
	public function resetFailedAttempts(Account $account): void
	{
		$cache = $this->app()->cache('', true, false);
		$cache?->deleteItem("sylphian_verify_failed_attempts_{$account->account_id}");
	}
}
