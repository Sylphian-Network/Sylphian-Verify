<?php

namespace Sylphian\Verify\Service\ServerStatus;

use Sylphian\Verify\Entity\GameServer;
use XF\Service\AbstractService;

class Fetcher extends AbstractService
{
	public function getStatus(GameServer $server)
	{
		$cache = $this->app->cache('', true, false);
		if (!$cache)
		{
			return $this->performLiveQuery($server);
		}

		$cacheKey = 'sylphian_verify_status_' . $server->server_id;

		$item = $cache->getItem($cacheKey);

		if ($item->isHit())
		{
			return $item->get();
		}

		$status = $this->performLiveQuery($server);

		$item->set($status);
		$item->expiresAfter(300);
		$cache->save($item);

		return $status;
	}

	protected function performLiveQuery(GameServer $server): array
	{
		return [
			'online' => true,
			'players' => 15,
			'max_players' => 100,
			'motd' => 'Example motd',
			'icon' => '',
			'favicon' => '',
			'last_check' => \XF::$time,
		];
	}
}
