<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

class RequestHandlerResult
{
	public static function success(ApiResult $result): self {
		return new RequestHandlerResult(true, null, $result);
	}

	public static function failure(?string $message): self {
		return new RequestHandlerResult(false, $message, null);
	}

	public function __construct(
		public readonly bool $success,
		public readonly ?string $message,
		public readonly ?ApiResult $result,
	)
	{
	}
}
