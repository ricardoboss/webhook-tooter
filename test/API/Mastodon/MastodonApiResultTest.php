<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API\Mastodon;

use PHPUnit\Framework\TestCase;

/**
 * @covers \ricardoboss\WebhookTooter\API\Mastodon\MastodonApiResponse
 *
 * @internal
 */
class MastodonApiResultTest extends TestCase {
	public function mastodonStatusProvider(): iterable {
		$status = ['url' => 'https://phpc.social/@ricardoboss/110045020216088409'];

		yield [$status, 'https://phpc.social/@ricardoboss/110045020216088409'];

		// TODO: add more test cases
	}

	/**
	 * @dataProvider mastodonStatusProvider
	 */
	public function testGetUrl(array $status, ?string $expectedUrl): void {
		$result = new MastodonApiResponse($status);
		$url = $result->getUrl();

		static::assertEquals($expectedUrl, $url);
	}
}
