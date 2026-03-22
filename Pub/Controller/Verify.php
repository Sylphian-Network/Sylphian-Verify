<?php

namespace Sylphian\Verify\Pub\Controller;

use Sylphian\Verify\Repository\GameServerRepository;
use XF\Mvc\Controller;
use XF\Mvc\Reply\View;

class Verify extends Controller
{
	public function actionIndex(): View
	{
		$serverRepo = $this->repository(GameServerRepository::class);
		$servers = $serverRepo->findServersForList()->fetch();

		$viewParams = [
			'servers' => $servers,
		];

		return $this->view('Sylphian\Verify:Verify', 'sylphian_verify_game_servers', $viewParams);
	}
}
