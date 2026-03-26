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

		$viewParams = [
			'categories' => $categories,
			'servers' => $servers,
		];

		return $this->view('Sylphian\Verify:Verify', 'sylphian_verify_game_servers', $viewParams);
	}
}
