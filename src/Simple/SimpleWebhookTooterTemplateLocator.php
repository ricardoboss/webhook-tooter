<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use InvalidArgumentException;
use ricardoboss\WebhookTooter\WebhookTooterTemplate;
use ricardoboss\WebhookTooter\WebhookTooterTemplateLocator;
use RuntimeException;

class SimpleWebhookTooterTemplateLocator implements WebhookTooterTemplateLocator
{
	public function __construct(
		private readonly string $templatesDirectory,
		private readonly string $templateExtension = '.md',
	)
	{
	}

	public function getMatchingTemplate(array $data): ?WebhookTooterTemplate
	{
		if (!isset($data['event'])) {
			throw new InvalidArgumentException("Missing 'event' key in payload");
		}

		$event = $data['event'];

		$templateFile = $this->templatesDirectory . '/' . $event . $this->templateExtension;

		if (!file_exists($templateFile)) {
			return null;
		}

		return new SimpleWebhookTooterTemplate($templateFile);
	}

	public function getDefaultTemplate(): WebhookTooterTemplate
	{
		throw new RuntimeException('No default template available');
	}
}
