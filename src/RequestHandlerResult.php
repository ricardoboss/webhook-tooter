<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

class RequestHandlerResult
{
	public static function success(?string $url, ?object $note): self {
		return new RequestHandlerResult(true, null, $url, $note);
	}

	public static function failure(?string $message): self {
		return new RequestHandlerResult(false, $message, null, null);
	}

	public function __construct(
		public readonly bool $success,
		public readonly ?string $message,
		public readonly ?string $url,
		public readonly ?object $note,
	)
	{
	}
}
