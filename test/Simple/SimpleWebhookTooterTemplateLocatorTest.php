<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\Simple;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ricardoboss\WebhookTooter\WebhookTooterTemplate;
use RuntimeException;

/**
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterTemplateLocator
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterTemplate
 *
 * @internal
 */
class SimpleWebhookTooterTemplateLocatorTest extends TestCase {
	public function eventNamesProvider(): iterable {
		yield ['test', "This is a test template.\n"];
		yield ['data', "Data: {{ data }}\n"];
	}

	public function getLocator(): SimpleWebhookTooterTemplateLocator {
		return new SimpleWebhookTooterTemplateLocator(dirname(__DIR__ ) . '/templates');
	}

	/**
	 * @dataProvider eventNamesProvider
	 */
	public function testResolvesByEventName(string $eventName, string $expectedContents): void {

		$locator = $this->getLocator();
		$template = $locator->getMatchingTemplate(['event' => $eventName]);
		static::assertInstanceOf(WebhookTooterTemplate::class, $template);
		static::assertEquals($expectedContents, $template->getContents());
	}

	public function testThrowsForMissingTemplate(): void {
		$locator = $this->getLocator();
		$invalidTemplate = $locator->getMatchingTemplate(['event' => 'invalid']);
		static::assertNull($invalidTemplate);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("No default template available");
		$locator->getDefaultTemplate();
	}

	public function testThrowsForMissingEventKey(): void {
		$locator = $this->getLocator();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Missing 'event' key in payload");

		$locator->getMatchingTemplate(['missing' => 'key']);
	}
}
