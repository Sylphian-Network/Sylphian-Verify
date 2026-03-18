<?php

namespace Sylphian\Verify\Pub\Controller;

use Sylphian\Verify\Entity\GameServer;
use Sylphian\Verify\Repository\GameServerRepository;
use XF\Mvc\Controller;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Exception;
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

	public function actionStatus(): AbstractReply
	{
		$serverId = $this->filter('server_id', 'uint');
		$server = $this->assertServerExists($serverId);

		$viewParams = [
			'server' => $server,
		];

		return $this->view('Sylphian\Verify:Verify\Status', 'sylphian_verify_game_server_status', $viewParams);
	}

	/**
	 * @param int $id
	 * @param array $with
	 * @param string|null $phraseKey
	 * @return GameServer
	 * @throws Exception
	 */
	protected function assertServerExists(int $id, array $with = [], ?string $phraseKey = null): GameServer
	{
		return $this->assertRecordExists('Sylphian\Verify:GameServer', $id, $with, $phraseKey);
	}
}
