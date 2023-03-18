<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface WebhookTooterTemplate
{
	public function getContents(): string;
}
