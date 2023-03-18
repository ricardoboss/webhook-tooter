<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

use Stringable;

class WebhookTooterConfig {
	public function __construct(
		public readonly string|Stringable|null $webhookPath = null,
		public readonly string|Stringable|null $webhookSecret = null,
	) {}
}
