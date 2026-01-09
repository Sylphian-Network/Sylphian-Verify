<?php

namespace Sylphian\Verify\Api\Controller;

use Sylphian\Library\Logger\AddonLogger;
use Sylphian\Library\Logger\Logger;
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
		$uuidRaw = $this->filter('uuid', 'str');
		$repo = $this->getVerificationRepo();
		$envelopeRepo = $this->getEnvelopeRepo();

		if (!$uuidRaw)
		{
			$this->logger->warning("API Request failed: Missing UUID");
			return $envelopeRepo->apiEnvelopeError('Please provide a UUID', ['uuid' => ['UUID is required']]);
		}

		$uuid = $repo->normaliseMinecraftUuid($uuidRaw);
		if (!$uuid)
		{
			$this->logger->warning("API Request failed: Invalid UUID format ({uuid})", ['uuid' => $uuidRaw]);
			return $envelopeRepo->apiEnvelopeError('Invalid UUID format', ['uuid' => ['Invalid UUID format']]);
		}

		$account = $repo->getAccountByMinecraftUuid($uuid);

		if (!$account || !$account->User)
		{
			$this->logger->info("API Request: UUID not found ({uuid})", ['uuid' => $uuid]);
			return $envelopeRepo->apiEnvelopeError('UUID not linked to any forum account');
		}

		if ($account->confirmed)
		{
			$this->logger->debug("API Request: User retrieved successfully ({uuid})", [
				'uuid' => $uuid,
				'username' => $account->User->username,
			]);

			return $envelopeRepo->apiEnvelopeSuccess([
				'id' => $account->account_id,
				'forum_username' => $account->User->username,
				'minecraft_username' => $account->username,
				'link_date' => $account->add_date,
				'confirmed_date' => $account->confirmed_date,
			], 'User retrieved successfully');
		}

		$passcode = $repo->getPasscode($account);

		$this->logger->info("API Request: Unconfirmed account found ({uuid})", ['uuid' => $uuid]);

		return $envelopeRepo->apiEnvelopeSuccess([
			'allowed' => false,
			'reason' => 'Account not confirmed',
			'passcode' => $passcode,
			'forum_username' => $account->User->username,
			'minecraft_username' => $account->username,
		], 'Account found but confirmation required');
	}

	/**
	 * @return VerificationRepository
	 */
	protected function getVerificationRepo(): VerificationRepository
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->repository('Sylphian\Verify:Verification');
	}

	/**
	 * @return EnvelopeRepository
	 */
	protected function getEnvelopeRepo(): EnvelopeRepository
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->repository('Sylphian\Verify:Envelope');
	}
}
