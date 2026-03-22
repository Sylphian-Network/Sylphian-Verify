<?php

namespace Sylphian\Verify\Service\ServerStatus;

use Sylphian\Verify\Entity\GameServer;
use Sylphian\Verify\Enum\GameType;
use Sylphian\Verify\ServerStatus\AbstractProvider;
use Sylphian\Verify\ServerStatus\Minecraft;
use XF\Service\AbstractService;

class Fetcher extends AbstractService
{
	protected const int CACHE_TTL = 3600;

	public function getStatus(GameServer $server): array
	{
		$cache = $this->app->cache();
		if (!$cache)
		{
			return [];
		}

		$data = $cache->fetch($this->getCacheKey($server));
		return is_array($data) ? $data : [];
	}

	public function refreshStatus(GameServer $server): array
	{
		$provider = $this->getProvider($server->game);
		if ($provider)
		{
			$status = $provider->fetchStatus($server);
		}
		else
		{
			$status = [
				'online' => false,
				'players' => 0,
				'max_players' => 0,
				'motd' => 'Unsupported game type',
				'icon' => '',
				'favicon' => '',
			];
		}

		$status['last_updated'] = \XF::$time;

		$cache = $this->app->cache();
		if ($cache)
		{
			$cache->save($this->getCacheKey($server), $status, self::CACHE_TTL);
		}

		return $status;
	}

	protected function getCacheKey(GameServer $server): string
	{
		return 'sylphian_verify_status_' . $server->server_id;
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
