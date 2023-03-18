<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API\Twitter;

use ricardoboss\WebhookTooter\ApiResult;

class TwitterApiResult implements ApiResult {
	public readonly object $tweet;

	public function __construct(object $tweet) {
		$this->tweet = $tweet;
	}

	public function getUrl(): ?string {
		return sprintf('https://twitter.com/%s/status/%s', $this->tweet->user->screen_name, $this->tweet->data->id);
	}
}
