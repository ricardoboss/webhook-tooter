<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

use Stringable;

class WebhookConfig {
	public function __construct(
		public readonly string|Stringable|null $path = null,
		public readonly string|Stringable|null $secret = null,
	) {}
}
