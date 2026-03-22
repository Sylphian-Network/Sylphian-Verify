<?php

namespace Sylphian\Verify\Entity;

use Sylphian\Verify\Enum\GameType;
use Sylphian\Verify\Service\ServerStatus\Fetcher;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use XF\Phrase;

/**
 * COLUMNS
 * @property int|null $server_id
 * @property string $title
 * @property string $game
 * @property string $host
 * @property int $port
 *
 * GETTERS
 * @property string $motd
 * @property int $players
 * @property int $max_players
 * @property bool $online
 * @property string $icon
 * @property string $favicon
 * @property string $game_label
 */
class GameServer extends Entity
{
	protected $_status = null;

	protected function getCachedStatus()
	{
		if ($this->_status === null)
		{
			$fetcher = \XF::app()->service(Fetcher::class);
			$this->_status = $fetcher->getStatus($this);
		}
		return $this->_status;
	}

	public function getMotd(): string
	{
		return (string) ($this->getCachedStatus()['motd'] ?? '');
	}

	public function getPlayers(): int
	{
		return $this->getCachedStatus()['players'] ?? 0;
	}

	public function getMaxPlayers(): int
	{
		return (int) ($this->getCachedStatus()['max_players'] ?? 0);
	}

	public function getOnline(): bool
	{
		return $this->getCachedStatus()['online'] ?? false;
	}

	public function getIcon(): string
	{
		return (string) ($this->getCachedStatus()['icon'] ?? '');
	}

	public function getFavicon(): string
	{
		return (string) ($this->getCachedStatus()['favicon'] ?? '');
	}

	public function getGameLabel(): Phrase|string
	{
		$game = GameType::tryFrom((string) $this->game);
		if ($game)
		{
			return $game->label();
		}

		return $this->game;
	}

	public static function getStructure(Structure $structure): Structure
	{
		$structure->table = 'xf_sylphian_verify_server';
		$structure->shortName = 'Sylphian\Verify:GameServer';
		$structure->primaryKey = 'server_id';
		$structure->columns = [
			'server_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'title' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
			'game' => ['type' => self::STR, 'maxLength' => 50, 'required' => true],
			'host' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
			'port' => ['type' => self::UINT, 'required' => true, 'default' => 25565],
		];
		$structure->getters = [
			'motd' => true,
			'players' => true,
			'max_players' => true,
			'online' => true,
			'icon' => true,
			'favicon' => true,
			'game_label' => true,
		];
		$structure->relations = [];

		return $structure;
	}
}
