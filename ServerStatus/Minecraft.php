<?php

namespace Sylphian\Verify\ServerStatus;

use GuzzleHttp\Exception\GuzzleException;
use Sylphian\Verify\Entity\GameServer;

class Minecraft extends AbstractProvider
{
	protected function getRateLimit(): array
	{
		$rateLimit = \XF::options()->sylphian_verify_minecraft_rate_limiting;
		return [$rateLimit['requests'] ?? 5, $rateLimit['window_in_seconds'] ?? 15];
	}

	protected function doFetchStatus(GameServer $server): array
	{
		$client = \XF::app()->http()->client();
		$status = $this->getDefaultStatus();

		$apiUrl = \XF::options()->sylphian_verify_minecraft_api_url;
		$url = str_replace(['{ip}', '{port}'], [$server->host, $server->port], $apiUrl);

		try
		{
			$response = $client->get(
				$url,
				['timeout' => 5]
			);

			$data = json_decode($response->getBody()->getContents(), true);

			if ($data && !empty($data['online']))
			{
				$status['online'] = true;
				$status['players'] = $data['players']['online'] ?? 0;
				$status['max_players'] = $data['players']['max'] ?? 0;

				if (isset($data['motd']['html']))
				{
					$html = implode('<br>', (array) $data['motd']['html']);
					$status['motd'] = strip_tags($html, '<span><br>');
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
