<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API\Mastodon;

use ricardoboss\WebhookTooter\ApiResponse;

class MastodonApiResponse implements ApiResponse {
	public readonly array $note;

	public function __construct(array $note) {
		$this->note = $note;
	}

	public function getUrl(): string {
		return $this->note['url'];
	}
}
