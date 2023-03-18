<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API\Twitter;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \ricardoboss\WebhookTooter\API\Twitter\TwitterApiResult
 *
 * @internal
 */
class TwitterApiResultTest extends TestCase {
	public function tweetObjectProvider(): iterable {
		$tweetObject = new stdClass();
		$tweetObject->user = new stdClass();
		$tweetObject->user->screen_name = 'test';
		$tweetObject->data = new stdClass();
		$tweetObject->data->id = 12345;

		yield [$tweetObject, 'https://twitter.com/test/status/12345'];

		// TODO: add more test cases
	}

	/**
	 * @dataProvider tweetObjectProvider
	 */
	public function testGetTweetUrl(object $tweet, ?string $expectedUrl): void {
		$result = new TwitterApiResult($tweet);
		$url = $result->getUrl();

		static::assertEquals($expectedUrl, $url);
	}
}
