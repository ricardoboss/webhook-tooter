<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use ricardoboss\WebhookTooter\WebhookTooterRenderer;
use ricardoboss\WebhookTooter\WebhookTooterTemplate;

class SimpleWebhookTooterRenderer implements WebhookTooterRenderer
{
	public function render(WebhookTooterTemplate $template, array $data): string
	{
		$text = $template->getContents();

		foreach ($data as $name => $item) {
			$text = str_replace("{{ $name }}", $item, $text);
		}

		return $text;
	}
}
