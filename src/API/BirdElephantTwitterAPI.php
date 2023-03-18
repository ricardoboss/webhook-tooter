<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API;

use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use ricardoboss\WebhookTooter\WebhookTooterAPI;

class BirdElephantTwitterAPI implements WebhookTooterAPI
{
	private array $credentials = [];

	/**
	 * @codeCoverageIgnore
	 */
	public function setCredentials(array $credentials): void
	{
		$this->credentials = $credentials;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function send(string $message): object
	{
		$twitter = new BirdElephant($this->credentials);
		$tweet = (new Tweet)->text($message);

		return $twitter->tweets()->tweet($tweet);
	}

	public function getUrl(object $note): ?string {
		return sprintf('https://twitter.com/%s/status/%s', $note->user->screen_name, $note->data->id);
	}
}
