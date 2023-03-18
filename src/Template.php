<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

interface Template
{
	public function getContents(): string;
}
