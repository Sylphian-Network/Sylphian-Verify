<?php

namespace Sylphian\Verify\Repository;

use XF\Api\Mvc\Reply\ApiResult;
use XF\Api\Result\ArrayResult;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\AbstractReply;

class EnvelopeRepository extends Repository
{
	/**
	 * Formats a successful API response using the Envelope Style.
	 *
	 * @param array|null $data
	 * @param string $message
	 * @param array $meta
	 * @return AbstractReply
	 */
	public function apiEnvelopeSuccess(?array $data, string $message = '', array $meta = []): AbstractReply
	{
		$result = new ArrayResult([
			'success' => true,
			'data' => $data,
			'message' => $message,
			'errors' => null,
			'meta' => $this->getEnvelopeMeta($meta),
		]);

		return new ApiResult($result);
	}

	/**
	 * Formats an error API response using the Envelope Style.
	 *
	 * @param string $message
	 * @param array $errors
	 * @param int $code
	 * @param array $meta
	 * @return AbstractReply
	 */
	public function apiEnvelopeError(string $message, array $errors = [], int $code = 400, array $meta = []): AbstractReply
	{
		$result = new ArrayResult([
			'success' => false,
			'data' => null,
			'message' => $message,
			'errors' => $errors ?: null,
			'meta' => $this->getEnvelopeMeta($meta),
		]);

		$reply = new ApiResult($result);
		$reply->setResponseCode($code);
		return $reply;
	}

	/**
	 * Generates the standard meta block for the envelope.
	 *
	 * @param array $extra
	 * @return array
	 */
	protected function getEnvelopeMeta(array $extra = []): array
	{
		return array_merge([
			'request_id' => bin2hex(random_bytes(16)),
			'timestamp' => date('c'),
		], $extra);
	}
}
