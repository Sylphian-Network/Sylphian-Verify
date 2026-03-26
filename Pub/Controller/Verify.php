<?php

namespace Sylphian\Verify\Pub\Controller;

use Sylphian\Verify\Repository\CategoryRepository;
use Sylphian\Verify\Repository\GameServerRepository;
use XF\Mvc\Controller;
use XF\Mvc\Reply\View;

class Verify extends Controller
{
	public function actionIndex(): View
	{
		$serverRepo = $this->repository(GameServerRepository::class);
		$categoryRepo = $this->repository(CategoryRepository::class);

		$categories = $categoryRepo->findCategoriesForList()->fetch();
		$servers = $serverRepo->findServersForList()->fetch();

		$serversGrouped = $servers->groupBy('category_id');

		$viewParams = [
			'categories' => $categories,
			'serversGrouped' => $serversGrouped,
		];

		return $this->view('Sylphian\Verify:Verify', 'sylphian_verify_game_servers', $viewParams);
	}
}
