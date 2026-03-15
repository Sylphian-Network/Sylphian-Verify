<?php

namespace Sylphian\Verify\ServerStatus;

use Sylphian\Verify\Entity\GameServer;

abstract class AbstractProvider
{
	abstract public function fetchStatus(GameServer $server): array;

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
