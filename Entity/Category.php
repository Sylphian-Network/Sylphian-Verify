<?php

namespace Sylphian\Verify\Entity;

use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null $category_id
 * @property string $title
 * @property string $description
 * @property int $display_order
 *
 * RELATIONS
 * @property AbstractCollection|GameServer[] $GameServers
 */
class Category extends Entity
{
	public function getId()
	{
		return $this->category_id;
	}

	public static function getStructure(Structure $structure): Structure
	{
		$structure->table = 'xf_sylphian_verify_server_categories';
		$structure->shortName = 'Sylphian\Verify:Category';
		$structure->primaryKey = 'category_id';
		$structure->columns = [
			'category_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'title' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
			'description' => ['type' => self::STR, 'default' => ''],
			'display_order' => ['type' => self::UINT, 'default' => 1],
		];
		$structure->getters = [
			'id' => true,
		];
		$structure->relations = [
			'GameServers' => [
				'entity' => 'Sylphian\Verify:GameServer',
				'type' => self::TO_MANY,
				'conditions' => 'category_id',
				'key' => 'server_id',
			],
		];

		return $structure;
	}
}
