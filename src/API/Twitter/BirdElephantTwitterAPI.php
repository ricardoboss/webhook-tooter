<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API\Twitter;

use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use ricardoboss\WebhookTooter\ApiService;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class BirdElephantTwitterAPI implements ApiService
{
	private array $credentials = [];

	public function setCredentials(array $credentials): void
	{
		$this->credentials = $credentials;
	}

	public function send(string $message): TwitterApiResponse
	{
		$twitter = new BirdElephant($this->credentials);
		$tweet = (new Tweet())->text($message);

		try {
			$tweetResult = $twitter->tweets()->tweet($tweet);
		} catch (Throwable $t) {
			throw new TwitterApiException("Error thrown by API: " . $t->getMessage(), previous: $t);
		}

		return new TwitterApiResponse($tweetResult);
	}
}
