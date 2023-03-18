<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface TemplateRenderer {
	public function render(Template $template, array $data): string;
}
