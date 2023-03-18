<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface TemplateLocator
{
	public function getMatchingTemplate(array $data): ?Template;

	public function getDefaultTemplate(): Template;
}
