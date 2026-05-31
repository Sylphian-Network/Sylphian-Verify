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
		$envelopeRepo = $this->getEnvelopeRepo();

		$input = $this->request->getInput();
		if (!array_key_exists('uuid', $input) && !array_key_exists('uuids', $input))
		{
			return $envelopeRepo->apiEnvelopeSuccess([
				'status'  => 'ok',
				'time'    => \XF::$time,
			], 'Minecraft Verification API is online');
		}

		$uuidRaw  = $this->filter('uuid', 'str');
		$uuidsRaw = $this->filter('uuids', 'array-str');

		$error = $this->validateUuidInputs($uuidRaw, $uuidsRaw);
		if ($error)
		{
			return $envelopeRepo->apiEnvelopeError($error);
		}

		$inputs  = $uuidRaw ? [$uuidRaw] : $uuidsRaw;
		[$results, $logContext] = $this->buildResultsFromInputs($inputs);

		$this->logger->info("API Request: Minecraft Verification Completed", [
			'type' => $uuidRaw ? 'single' : 'batch',
			'count' => count($inputs),
			'results_summary' => $logContext,
		]);

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
		$logContext  = [];

		foreach ($inputs AS $orig)
		{
			$norm = $repo->normaliseMinecraftUuid($orig);
			if (!$norm)
			{
				$logContext[$orig] = 'INVALID_FORMAT';
				$results[$orig]    = [
					'allowed' => false,
					'reason'  => 'INVALID_UUID_FORMAT',
				];
				continue;
			}
			$validUuids[]         = $norm;
			$normToOrigs[$norm][] = $orig;
		}

		$uniqueValidUuids = array_unique($validUuids);
		if ($uniqueValidUuids)
		{
			[$results, $logContext] = $this->resolveAccountResults($uniqueValidUuids, $normToOrigs, $results, $logContext);
		}

		return [$results, $logContext];
	}

	protected function resolveAccountResults(array $uniqueValidUuids, array $normToOrigs, array $results, array $logContext): array
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
				$res       = [
					'allowed' => false,
					'reason'  => 'UUID_NOT_LINKED',
				];
				$logStatus = 'NOT_FOUND';
			}
			else
			{
				[$res, $logStatus] = $this->getAccountResult($account);
			}

			foreach ($normToOrigs[$norm] AS $orig)
			{
				$results[$orig]    = $res;
				$logContext[$orig] = $logStatus;
			}
		}

		return [$results, $logContext];
	}

	protected function getAccountResult(Account $account): array
	{
		$repo = $this->getVerificationRepo();

		if ($account->confirmed)
		{
			return [
				[
					'allowed'            => true,
					'id'                 => $account->account_id,
					'forum_user_id'      => $account->User->user_id,
					'forum_username'     => $account->User->username,
					'minecraft_username' => $account->username,
					'link_date'          => $account->add_date,
					'confirmed_date'     => $account->confirmed_date,
				],
				'SUCCESS',
			];
		}

		$bruteForce = $repo->getBruteForceDetails($account);
		if ($bruteForce['is_blocked'])
		{
			return [
				[
					'allowed'            => false,
					'reason'             => 'BRUTE_FORCE_BLOCKED',
					'block_expires'      => $bruteForce['block_expires'],
					'forum_user_id'      => $account->User->user_id,
					'forum_username'     => $account->User->username,
					'minecraft_username' => $account->username,
				],
				'BRUTE_FORCE_BLOCKED',
			];
		}

		$passcodeDetails = $repo->getPasscodeDetails($account);

		return [
			[
				'allowed'            => false,
				'reason'             => 'ACCOUNT_NOT_CONFIRMED',
				'passcode'           => $passcodeDetails['passcode'],
				'passcode_expires'   => $passcodeDetails['expires'],
				'attempts_remaining' => $bruteForce['attempts_remaining'],
			],
			'UNCONFIRMED',
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
