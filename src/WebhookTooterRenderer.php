<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface WebhookTooterRenderer {
	public function render(WebhookTooterTemplate $template, array $data): string;
}
