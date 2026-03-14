<?php

namespace Sylphian\Verify\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class GameServerRepository extends Repository
{
	public function findServersForList(): Finder
	{
		return $this->finder('Sylphian\Verify:GameServer')
			->order('title', 'ASC');
	}
}
