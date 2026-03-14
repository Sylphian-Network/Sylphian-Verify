<?php

namespace Sylphian\Verify\Model;

readonly class GameServer
{
    public function __construct(
        public string $title,
        public string $motd,
        public string $game,
        public string $host,
        public int $port,
        public int $max_players,
        public int $players,
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
            'game' => $this->game,
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
        return new self(
            title: (string) ($data['title'] ?? ''),
            motd: (string) ($data['motd'] ?? ''),
            game: (string) ($data['game'] ?? ''),
            host: (string) ($data['host'] ?? ''),
            port: (int) ($data['port'] ?? 0),
            max_players: (int) ($data['max_players'] ?? 0),
            players: (int) ($data['players'] ?? 0),
            online: (bool) ($data['online'] ?? true),
            icon: (string) ($data['icon'] ?? ''),
            favicon: (string) ($data['favicon'] ?? '')
        );
    }
}
