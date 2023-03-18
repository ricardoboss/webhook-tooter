<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface WebhookTooterTemplateLocator
{
	public function getMatchingTemplate(array $data): ?WebhookTooterTemplate;

	public function getDefaultTemplate(): WebhookTooterTemplate;
}
