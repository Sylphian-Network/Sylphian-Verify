<?php

namespace Sylphian\Verify\ServerStatus;

use Sylphian\Verify\Entity\GameServer;

abstract class AbstractProvider
{
	protected static array $requestHistory = [];

	public function fetchStatus(GameServer $server): array
	{
		$this->throttle();

		return $this->doFetchStatus($server);
	}

	abstract protected function doFetchStatus(GameServer $server): array;

	protected function throttle(): void
	{
		[$limit, $window] = $this->getRateLimit();
		if ($limit <= 0 || $window <= 0)
		{
			return;
		}

		$class = static::class;
		if (!isset(self::$requestHistory[$class]))
		{
			self::$requestHistory[$class] = [];
		}

		$now = microtime(true);
		$windowStart = $now - $window;

		self::$requestHistory[$class] = array_filter(
			self::$requestHistory[$class],
			fn ($time) => $time > $windowStart
		);

		if (count(self::$requestHistory[$class]) >= $limit)
		{
			$oldest = min(self::$requestHistory[$class]);
			$sleepUntil = $oldest + $window;
			$sleepTime = ($sleepUntil - $now) * 1000000;

			if ($sleepTime > 0)
			{
				usleep((int) $sleepTime);
				$now = microtime(true);
			}
		}

		self::$requestHistory[$class][] = $now;
	}

	/**
	 * Returns the rate limit as [requests, window_in_seconds]
	 *
	 * @return array{0: int|float, 1: int|float}
	 */
	protected function getRateLimit(): array
	{
		return [0, 0];
	}

	protected function getDefaultStatus(): array
	{
		return [
			'online' => false,
			'players' => 0,
			'max_players' => 0,
			'motd' => '',
			'icon' => '',
			'favicon' => '',
		];
	}
}
