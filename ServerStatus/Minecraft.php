<?php

namespace Sylphian\Verify\ServerStatus;

use GuzzleHttp\Exception\GuzzleException;
use Sylphian\Verify\Entity\GameServer;

class Minecraft extends AbstractProvider
{
	protected const string API_URL = 'https://api.mcstatus.io/v2/status/java/%s:%d';

	public function fetchStatus(GameServer $server): array
	{
		$client = \XF::app()->http()->client();
		$status = $this->getDefaultStatus();

		try
		{
			$response = $client->get(
				sprintf(self::API_URL, $server->host, $server->port),
				['timeout' => 5]
			);

			$data = json_decode($response->getBody()->getContents(), true);

			if ($data && !empty($data['online']))
			{
				$status['online'] = true;
				$status['players'] = $data['players']['online'] ?? 0;
				$status['max_players'] = $data['players']['max'] ?? 0;

				if (isset($data['motd']['clean']))
				{
					$status['motd'] = implode("\n", (array) $data['motd']['clean']);
				}

				if (!empty($data['icon']))
				{
					$status['icon'] = $data['icon'];
					$status['favicon'] = $data['icon'];
				}
			}
		}
		catch (GuzzleException $e)
		{
		}

		return $status;
	}
}
