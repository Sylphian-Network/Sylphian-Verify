<?php

namespace Sylphian\Verify\Service\ServerStatus;

use Sylphian\Verify\Entity\GameServer;
use Sylphian\Verify\Enum\GameType;
use Sylphian\Verify\ServerStatus\AbstractProvider;
use Sylphian\Verify\ServerStatus\Minecraft;
use XF\Service\AbstractService;

class Fetcher extends AbstractService
{
	protected const int CACHE_TTL = 300;

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
		$item->expiresAfter(self::CACHE_TTL);
		$cache->save($item);

		return $status;
	}

	protected function performLiveQuery(GameServer $server): array
	{
		$provider = $this->getProvider($server->game);
		if (!$provider)
		{
			return [
				'online' => false,
				'players' => 0,
				'max_players' => 0,
				'motd' => 'Unsupported game type',
				'icon' => '',
				'favicon' => '',
			];
		}

		return $provider->fetchStatus($server);
	}

	protected function getProvider(string $gameType): ?AbstractProvider
	{
		return match ($gameType)
		{
			GameType::MINECRAFT->value => new Minecraft(),
			default => null,
		};

	}
}
