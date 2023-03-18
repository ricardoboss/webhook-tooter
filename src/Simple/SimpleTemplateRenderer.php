<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use ricardoboss\WebhookTooter\TemplateRenderer;
use ricardoboss\WebhookTooter\Template;

class SimpleTemplateRenderer implements TemplateRenderer
{
	public function render(Template $template, array $data): string
	{
		$text = $template->getContents();

		foreach ($data as $name => $item) {
			$text = str_replace("{{ $name }}", $item, $text);
		}

		return $text;
	}
}
