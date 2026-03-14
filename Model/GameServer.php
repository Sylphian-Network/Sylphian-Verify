<?php

namespace Sylphian\Verify\Model;

use Sylphian\Verify\Enum\GameType;

readonly class GameServer
{
	public function __construct(
		public string $title,
		public string $motd,
		public GameType|string $game,
		public ?string $host = null,
		public ?int $port = null,
		public int $max_players = 0,
		public int $players = 0,
		public bool $online = true,
		public string $icon = '',
		public string $favicon = ''
	)
	{
	}

	public function toArray(): array
	{
		return [
			'title' => $this->title,
			'motd' => $this->motd,
			'game' => $this->game instanceof GameType ? $this->game->value : $this->game,
			'game_label' => $this->game instanceof GameType ? $this->game->label() : $this->game,
			'host' => $this->host,
			'port' => $this->port,
			'max_players' => $this->max_players,
			'players' => $this->players,
			'online' => $this->online,
			'icon' => $this->icon,
			'favicon' => $this->favicon,
		];
	}

	public static function fromArray(array $data): self
	{
		$game = $data['game'] ?? '';
		if (is_string($game))
		{
			$game = GameType::tryFrom($game) ?? $game;
		}

		return new self(
			title: (string) ($data['title'] ?? ''),
			motd: (string) ($data['motd'] ?? ''),
			game: $game,
			host: isset($data['host']) ? (string) $data['host'] : null,
			port: isset($data['port']) ? (int) $data['port'] : null,
			max_players: (int) ($data['max_players'] ?? 0),
			players: (int) ($data['players'] ?? 0),
			online: (bool) ($data['online'] ?? true),
			icon: (string) ($data['icon'] ?? ''),
			favicon: (string) ($data['favicon'] ?? '')
		);
	}
}
