<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface WebhookTooterAPI
{
	public function send(string $message): object;
	public function getUrl(object $note): ?string;
}
