<?php

namespace Sylphian\Verify\Cron;

use Sylphian\Verify\Service\ServerStatus\Fetcher;

class ServerStatusUpdate
{
	public static function runServerStatusUpdate(): void
	{
		$app = \XF::app();

		$servers = $app->finder('Sylphian\Verify:GameServer')->fetch();
		$fetcher = $app->service(Fetcher::class);

		foreach ($servers AS $server)
		{
			$fetcher->refreshStatus($server);
		}
	}
}
