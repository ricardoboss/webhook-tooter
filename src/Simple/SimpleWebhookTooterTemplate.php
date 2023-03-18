<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use ricardoboss\WebhookTooter\WebhookTooterTemplate;

class SimpleWebhookTooterTemplate implements WebhookTooterTemplate
{
	public function __construct(private readonly string $templateFile)
	{
	}

	public function getContents(): string
	{
		return file_get_contents($this->templateFile);
	}
}
