<?php

namespace Sylphian\Verify\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class CategoryRepository extends Repository
{
	public function findCategoriesForList(): Finder
	{
		return $this->finder('Sylphian\Verify:Category')
			->order('display_order', 'ASC');
	}
}
