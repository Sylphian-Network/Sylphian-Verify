<?php

namespace Sylphian\Verify\Entity;

use Sylphian\Verify\Enum\GameType;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

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
	public function getMotd(): string
	{
		// TODO: Implement dynamic fetching
		return "Default MOTD for " . $this->title;
	}

	public function getPlayers(): int
	{
		// TODO: Implement dynamic fetching
		return 0;
	}

	public function getMaxPlayers(): int
	{
		// TODO: Implement dynamic fetching
		return 0;
	}

	public function getOnline(): bool
	{
		// TODO: Implement dynamic fetching
		return true;
	}

	public function getIcon(): string
	{
		// TODO: Implement dynamic fetching
		return rand(0, 1) ? "https://api.mcstatus.io/v2/icon/mc.example.com" : "";
	}

	public function getFavicon(): string
	{
		// TODO: Implement dynamic fetching
		return rand(0, 1) ? "data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAZdEVYdFNvZnR3YXJlAFBhaW50Lk5FVCA1LjEuMTGKCBbOAAAAuGVYSWZJSSoACAAAAAUAGgEFAAEAAABKAAAAGwEFAAEAAABSAAAAKAEDAAEAAAADAAAAMQECABEAAABaAAAAaYcEAAEAAABsAAAAAAAAAKOTAADoAwAAo5MAAOgDAABQYWludC5ORVQgNS4xLjExAAADAACQBwAEAAAAMDIzMAGgAwABAAAAAQAAAAWgBAABAAAAlgAAAAAAAAACAAEAAgAEAAAAUjk4AAIABwAEAAAAMDEwMAAAAADY5TB4zfSjcAAADZdJREFUeF7V2/mvVdUVB/ANyDw7IAgCKogICooKFMcqioGEkkhjY5o0/ERq/4AmbZO2SX/rD00bmib8VLXRaJsYESVKIyqIyiQqTiCCA6KACijI+Lo+Wza5feXBu+dcU13J4lzuPWefvb7ru4a9z3ldUjtpa2vrEYdhoZNDp4bOCJ0UOiD0f87/DsvB0M2hq0JXh64P3RZ6oEuXLm1xzHLSoDDc50GhPwj9Uej00OGhvUO7h3YN/T4JI4+GHgrdHbohdEno8tAdAcKxOH4DwAnjh4Qy/GehE0P7hH5rRsc9sx4/fjwfi8TEsnbt2jUfWyRucDgUA/4Vel/oFiAUAAbH4cehvwgdF8rjLRfGHjlyJB08eDAdOHAgffnll2nfvn35s+8Z3KtXr9SvX7/Uv3//fOzdu3f+rlu3bq0A5HjoztB/hP4tdFuXMF7Mzwz9deiU0JYaz7tHjx7Nxn766afpo48+Slu3bk0ffPBB+uyzz9IXX3yRfzt8+HA2kMEDBgxIgwcPTueff366+OKL06hRo9KwYcPS2WefnXr27JnZUUOAsD30T6F/B8Co+PCr0J+E9gttmTCcge+99156/fXX08aNG9Pbb7+ddu7cmY1nNFa0F0AwEgOGDBmSRo4cma688so0adKkNH78+AwMVtRgxJHQlaG/AcDc+PCH0PGhLYl5Xkdrhr/00ktp1apV6dVXX80MYLTfmxH0B8bYsWPT9OnT00033ZQmTpyYBg4cWIcNu0L/AgDG/zxUBagtPPr555+nV155JS1btiw9//zzme7YUFd4XGhMmzYtzZ07N82YMSOdc845VUGQFNcCYEV8mBba07d1hPF79uxJK1euTI8++mh64YUX0v79+5v2+JlEnrjuuuvSPffck2688cY0aNCgKuEg9vaBTpNTO/ExUryj+0MPPZSeffbZnOFbbTzBps2bN6elS5emTZs2pUOHlPqmhe39/DPwxH9qyVdffZXWr1+fPS/ulbq60uhVNO/Ro0c+YsCECRPy76tXr865pSLQOXia5k77m6nh7777bnriiScyA5oxnhFnnXVWTnSN0r1793TBBRecjHFHVWDo0KE5kfbp0yddffXVOeTef//9PIcq0rTnjx07lpOc2l26OFR/8cUX03PPPZc/d1YYzzCGqPWNyYyhN9xwQ7rsssuy5y+99NJ8HlAYq8oojxdeeGH65JNPMihVpGkAxJ+aLtG5scmI/XfeeSd9/PHHJ846szBePb/55pvT1KlTMxBFUJy30VxTpCv0f80QwDVDrgVSAa5qrqkU+wyW6B577LFs+Ndff50pqS43xm1HYsI8N3PmzOxVIbNjx47MKOGg6ZHdNTu7d+9OkydPTldccUUeWwPl/9dcc00677zzMnCAwZIq0u23ISc+d0pM8sMPP0zLly9P69aty2WOR7SpVIj47lQxyYCSwG677bbc1TF8xYoVadu2bTnuGT9v3rxMeUwTbrNnz87e3rBhQ+rbt2/+/fLLL895o7ABUzoDfjtp0wc0xR0hINsvWrQoe4gnVYARI0bk+MSONWvW5JwgJABGSlJjIDXhLVu25LwhgQJmypQpac6cOZkVukhhxtPywPbt23MLDTT/B7Tk576AN34FOd40AAyymFm8eHFeqIhhtOQtiQkgJqM+8+wbb7yRExQvaWEvueSSXLfffPPN3C1Kpq7R2Nxyyy3ZQCD63ffjxo3LIbN3797sfWFmfNdZW4wZMyaPXcH7pHkAnL5r1670wAMP5OR011135YmhKo9StDz33HNPhopabdJozdM8J4FedNFF2cNXXXVVjmve5Flx77yy+itTZGQxFOvWrl2bneC6qgA0nQPKjRjM82IRnYHAE5THeWz48OE5S8sHKM1wse464/hddlcFMIPRgOJl4DoHq+QUBgNH3GMEFskfgC7nVpC2SlWAVxjmpjJ48ZDJycjXXnttBsYkAXLnnXfm+HZ+yQmM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" : "";
	}

	public function getGameLabel()
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
