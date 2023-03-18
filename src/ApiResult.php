<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface ApiResult {
	public function getUrl(): ?string;
}
