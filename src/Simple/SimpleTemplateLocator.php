<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use InvalidArgumentException;
use ricardoboss\WebhookTooter\Template;
use ricardoboss\WebhookTooter\TemplateLocator;
use RuntimeException;

class SimpleTemplateLocator implements TemplateLocator
{
	public function __construct(
		private readonly string $templatesDirectory,
		private readonly string $templateExtension = '.md',
	)
	{
	}

	public function getMatchingTemplate(array $data): ?Template
	{
		if (!isset($data['event'])) {
			throw new InvalidArgumentException("Missing 'event' key in payload");
		}

		$event = $data['event'];

		$templateFile = rtrim($this->templatesDirectory, '/') . '/' . $event . $this->templateExtension;

		if (!file_exists($templateFile)) {
			return null;
		}

		return new SimpleTemplate($templateFile);
	}

	public function getDefaultTemplate(): Template
	{
		throw new RuntimeException('No default template available');
	}
}
