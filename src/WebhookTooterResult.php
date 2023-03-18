<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

class WebhookTooterResult
{
	public static function success(?string $url, ?object $tweet): self {
		return new WebhookTooterResult(true, null, $url, $tweet);
	}

	public static function failure(?string $message): self {
		return new WebhookTooterResult(false, $message, null, null);
	}

	public function __construct(
		public readonly bool $success,
		public readonly ?string $message,
		public readonly ?string $url,
		public readonly ?object $tweet,
	)
	{
	}
}
