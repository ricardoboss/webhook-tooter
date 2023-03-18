<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use ricardoboss\WebhookTooter\Template;

class SimpleTemplate implements Template
{
	public function __construct(private readonly string $templateFile)
	{
	}

	public function getContents(): string
	{
		return file_get_contents($this->templateFile);
	}
}
