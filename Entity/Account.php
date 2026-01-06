<?php

namespace Sylphian\Verify\Entity;

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
