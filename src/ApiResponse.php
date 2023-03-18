<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface ApiResponse {
	public function getUrl(): string;
}
