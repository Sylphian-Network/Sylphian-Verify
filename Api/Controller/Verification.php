<?php

namespace Sylphian\Verify\Api\Controller;

use Sylphian\Library\Logger\AddonLogger;
use Sylphian\Library\Logger\Logger;
use Sylphian\Verify\Entity\Account;
use Sylphian\Verify\Repository\EnvelopeRepository;
use Sylphian\Verify\Repository\VerificationRepository;
use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;

class Verification extends AbstractController
{
	protected AddonLogger $logger;

	protected function preDispatchController($action, ParameterBag $params): void
	{
		$this->assertApiScopeByRequestMethod('sylphian_verify');
		$this->logger = Logger::withAddonId('Sylphian/Verify');
	}

	public function actionGetMinecraft(): AbstractReply
	{
		$uuidRaw  = $this->filter('uuid', 'str');
		$uuidsRaw = $this->filter('uuids', 'array-str');

		$envelopeRepo = $this->getEnvelopeRepo();

		$error = $this->validateUuidInputs($uuidRaw, $uuidsRaw);
		if ($error)
		{
			return $envelopeRepo->apiEnvelopeError($error);
		}

		$inputs  = $uuidRaw ? [$uuidRaw] : $uuidsRaw;
		$results = $this->buildResultsFromInputs($inputs);

		$data    = $uuidRaw ? ($results[$uuidRaw] ?? null) : $results;
		$message = $uuidRaw ? 'User retrieved successfully' : 'Users retrieved successfully';

		return $envelopeRepo->apiEnvelopeSuccess($data, $message);
	}

	protected function validateUuidInputs(string $uuidRaw, array $uuidsRaw): ?string
	{
		if ($uuidRaw && $uuidsRaw)
		{
			return 'Provide either uuid or uuids, not both';
		}

		if (!$uuidRaw && !$uuidsRaw)
		{
			return 'Please provide a uuid or uuids array';
		}

		return null;
	}

	protected function buildResultsFromInputs(array $inputs): array
	{
		$repo        = $this->getVerificationRepo();
		$results     = [];
		$validUuids  = [];
		$normToOrigs = [];

		foreach ($inputs AS $orig)
		{
			$norm = $repo->normaliseMinecraftUuid($orig);
			if (!$norm)
			{
				$this->logger->warning("API Request: Invalid UUID format ({uuid})", ['uuid' => $orig]);
				$results[$orig] = [
					'allowed' => false,
					'reason'  => 'INVALID_UUID_FORMAT',
				];
				continue;
			}
			$validUuids[]       = $norm;
			$normToOrigs[$norm][] = $orig;
		}

		$uniqueValidUuids = array_unique($validUuids);
		if ($uniqueValidUuids)
		{
			$results = $this->resolveAccountResults($uniqueValidUuids, $normToOrigs, $results);
		}

		return $results;
	}

	protected function resolveAccountResults(array $uniqueValidUuids, array $normToOrigs, array $results): array
	{
		$repo     = $this->getVerificationRepo();
		$accounts = $repo->getAccountsByMinecraftUuids($uniqueValidUuids);

		$accountsByNorm = [];
		foreach ($accounts AS $account)
		{
			$accountsByNorm[$account->provider_key] = $account;
		}

		foreach ($uniqueValidUuids AS $norm)
		{
			$account = $accountsByNorm[$norm] ?? null;

			if (!$account || !$account->User)
			{
				$this->logger->info("API Request: UUID not found ({uuid})", ['uuid' => $norm]);
				$res = [
					'allowed' => false,
					'reason'  => 'UUID_NOT_LINKED',
				];
			}
			else
			{
				$res = $this->getAccountResult($account);
			}

			foreach ($normToOrigs[$norm] AS $orig)
			{
				$results[$orig] = $res;
			}
		}

		return $results;
	}

	protected function getAccountResult(Account $account): array
	{
		$repo = $this->getVerificationRepo();

		if ($account->confirmed)
		{
			$this->logger->debug("API Request: User retrieved successfully ({uuid})", [
				'uuid' => $account->provider_key,
				'username' => $account->User->username,
			]);

			return [
				'allowed' => true,
				'id' => $account->account_id,
				'forum_user_id' => $account->User->user_id,
				'forum_username' => $account->User->username,
				'minecraft_username' => $account->username,
				'link_date' => $account->add_date,
				'confirmed_date' => $account->confirmed_date,
			];
		}

		$bruteForce = $repo->getBruteForceDetails($account);
		if ($bruteForce['is_blocked'])
		{
			$this->logger->warning("API Request: Brute force blocked ({uuid})", ['uuid' => $account->provider_key]);

			return [
				'allowed' => false,
				'reason' => 'BRUTE_FORCE_BLOCKED',
				'block_expires' => $bruteForce['block_expires'],
				'forum_user_id' => $account->User->user_id,
				'forum_username' => $account->User->username,
				'minecraft_username' => $account->username,
			];
		}

		$passcodeDetails = $repo->getPasscodeDetails($account);

		$this->logger->info("API Request: Unconfirmed account found ({uuid})", ['uuid' => $account->provider_key]);

		return [
			'allowed' => false,
			'reason' => 'ACCOUNT_NOT_CONFIRMED',
			'passcode' => $passcodeDetails['passcode'],
			'passcode_expires' => $passcodeDetails['expires'],
			'attempts_remaining' => $bruteForce['attempts_remaining'],
		];
	}

	protected function getVerificationRepo(): VerificationRepository
	{
		return $this->repository(VerificationRepository::class);
	}

	protected function getEnvelopeRepo(): EnvelopeRepository
	{
		return $this->repository(EnvelopeRepository::class);
	}
}
