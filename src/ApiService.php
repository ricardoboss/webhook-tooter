<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface ApiService
{
	public function send(string $message): ApiResponse;
}
