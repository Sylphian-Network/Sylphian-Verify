<?php

namespace Sylphian\Verify\Entity;

use Sylphian\Library\Logger\Logger;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property int|null $account_id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_key
 * @property string $username
 * @property int $add_date
 * @property bool $confirmed
 * @property int $confirmed_date
 *
 * @property User $User
 */
class Account extends Entity
{
	protected function _postSave(): void
	{
		$logger = Logger::withAddonId('Sylphian/Verify');
		$request = \XF::app()->request();

		$context = [
			'account_id' => $this->account_id,
			'user_id' => $this->user_id,
			'username' => $this->username,
			'provider' => $this->provider,
			'provider_key' => $this->provider_key,
			'ip' => $request->getIp(),
			'user_agent' => $request->getUserAgent(),
		];

		if ($this->isInsert())
		{
			$logger->info("Account link created: {username} ({provider}:{provider_key})", $context);
		}

		if ($this->isUpdate() && $this->isChanged('confirmed') && $this->confirmed)
		{
			$logger->info("Account link confirmed: {username} ({provider}:{provider_key})", $context);
		}
	}

	protected function _postDelete(): void
	{
		$logger = Logger::withAddonId('Sylphian/Verify');
		$request = \XF::app()->request();

		$logger->info("Account link removed: {username} ({provider}:{provider_key})", [
			'account_id' => $this->account_id,
			'user_id' => $this->user_id,
			'username' => $this->username,
			'provider' => $this->provider,
			'provider_key' => $this->provider_key,
			'ip' => $request->getIp(),
			'user_agent' => $request->getUserAgent(),
		]);
	}

	public static function getStructure(Structure $structure): Structure
	{
		$structure->table = 'xf_sylphian_verify_account';
		$structure->shortName = 'Sylphian\Verify:Account';
		$structure->primaryKey = 'account_id';
		$structure->columns = [
			'account_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'provider' => ['type' => self::STR, 'maxLength' => 50, 'required' => true],
			'provider_key' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
			'username' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
			'add_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'confirmed' => ['type' => self::BOOL, 'default' => false],
			'confirmed_date' => ['type' => self::UINT, 'default' => 0],
		];
		$structure->getters = [];
		$structure->relations = [
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true,
			],
		];

		return $structure;
	}
}
