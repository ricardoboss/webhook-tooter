<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

use JsonException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Mockery as M;
use ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterRenderer;
use ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterTemplateLocator;
use stdClass;
use Stringable;

/**
 * @covers \ricardoboss\WebhookTooter\WebhookTooterHandler
 * @covers \ricardoboss\WebhookTooter\WebhookTooterConfig
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterRenderer
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterTemplateLocator
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterTemplate
 * @covers \ricardoboss\WebhookTooter\WebhookTooterResult
 *
 * @internal
 */
class WebhookTooterHandlerTest extends TestCase
{
	/**
	 * @throws JsonException
	 */
	public function requestProvider(): iterable
	{
		$factory = new Psr17Factory();

		$config = new WebhookTooterConfig('/webhook', 'secret');
		$renderer = new SimpleWebhookTooterRenderer();
		$templateLocator = new SimpleWebhookTooterTemplateLocator(__DIR__ . '/templates');
		$twitter = M::mock(WebhookTooterAPI::class);

		$testData = [
			'event' => 'test',
		];
		$testDataJson = json_encode($testData, JSON_THROW_ON_ERROR);

		$testUsername = 'test';
		$testTweetId = '12345';
		$testTweetObject = new stdClass();
		$testTweetObject->user = new stdClass();
		$testTweetObject->user->screen_name = $testUsername;
		$testTweetObject->data = new stdClass();
		$testTweetObject->data->id = $testTweetId;
		$testTweetUrl = "https://twitter.com/$testUsername/status/$testTweetId";

		$baseRequest = $factory
			->createRequest('POST', 'https://example.com' . $config->webhookPath)
			->withHeader('Content-Type', 'application/json')
			->withBody($factory->createStream($testDataJson))
			->withHeader(WebhookTooterHandler::SignatureHeader, WebhookTooterHandler::SignatureAlgorithm . '=' . hash_hmac(WebhookTooterHandler::SignatureAlgorithm, $testDataJson, $config->webhookSecret));
		$successResult = new WebhookTooterResult(true, null, $testTweetUrl, $testTweetObject);

		$testDataWithTemplateData = [
			'event' => 'data',
			'data' => 'testdata',
		];
		$testDataWithTemplateDataJson = json_encode($testDataWithTemplateData, JSON_THROW_ON_ERROR);
		$baseRequestWithData = $baseRequest
			->withBody($factory->createStream($testDataWithTemplateDataJson))
			->withHeader(WebhookTooterHandler::SignatureHeader, WebhookTooterHandler::SignatureAlgorithm . '=' . hash_hmac(WebhookTooterHandler::SignatureAlgorithm, $testDataWithTemplateDataJson, $config->webhookSecret))
		;

		$twitter
			->expects('send')
			->with("This is a test template.\n")
			->andReturns($testTweetObject)
		;
		$twitter
			->expects('send')
			->with("Data: " . $testDataWithTemplateData['data'] . "\n")
			->andReturns($testTweetObject)
		;
		$twitter
			->expects('getUrl')
			->with($testTweetObject)
			->andReturns($testTweetUrl)
		;

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequest,
			'expected' => $successResult,
		];

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequestWithData,
			'expected' => $successResult,
		];

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequest,
			'expected' => $successResult,
		];

		$invalidMethodRequest = $baseRequest->withMethod('GET');
		$invalidMethodResult = new WebhookTooterResult(false, 'Invalid request method: GET', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidMethodRequest,
			'expected' => $invalidMethodResult,
		];

		$invalidPathRequest = $baseRequest->withUri(new Uri('https://example.com/not-the-webhook-path'));
		$invalidPathResult = new WebhookTooterResult(false, 'Invalid request path: /not-the-webhook-path', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidPathRequest,
			'expected' => $invalidPathResult,
		];

		$invalidSecretRequest = $baseRequest->withHeader(WebhookTooterHandler::SignatureHeader, 'not-the-signature');
		$invalidSecretResult = new WebhookTooterResult(false, 'Invalid request signature', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidSecretRequest,
			'expected' => $invalidSecretResult,
		];

		$invalidContentTypeRequest = $baseRequest->withHeader('Content-Type', 'application/x-www-form-urlencoded');
		$invalidContentTypeResult = new WebhookTooterResult(false, 'Invalid request content type: application/x-www-form-urlencoded', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidContentTypeRequest,
			'expected' => $invalidContentTypeResult,
		];

		$invalidContentRequest = $baseRequest
			->withBody($factory->createStream('invalid-json'))
			->withHeader(WebhookTooterHandler::SignatureHeader, WebhookTooterHandler::SignatureAlgorithm . '=' . hash_hmac(WebhookTooterHandler::SignatureAlgorithm, 'invalid-json', $config->webhookSecret))
		;
		$invalidContentResult = new WebhookTooterResult(false, 'Invalid request payload: Syntax error', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidContentRequest,
			'expected' => $invalidContentResult,
		];

		$configWithoutSecret = new WebhookTooterConfig('/webhook');
		$baseRequestWithoutSignature = $baseRequest->withoutHeader(WebhookTooterHandler::SignatureHeader);

		yield [
			'config' => $configWithoutSecret,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequestWithoutSignature,
			'expected' => $successResult,
		];

		$configWithStringable = new WebhookTooterConfig(new class implements Stringable {
			public function __toString(): string
			{
				return '/webhook';
			}
		});

		yield [
			'config' => $configWithStringable,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequestWithoutSignature,
			'expected' => $successResult,
		];
	}

	/**
	 * @dataProvider requestProvider
	 */
	public function testHandle(
		WebhookTooterConfig $config,
		WebhookTooterRenderer $renderer,
		WebhookTooterTemplateLocator $templateLocator,
		WebhookTooterAPI $api,
		RequestInterface $request,
		WebhookTooterResult $expected,
	): void
	{
		$request->getBody()->rewind();

		$handler = new WebhookTooterHandler($config, $renderer, $templateLocator, $api);
		$result = $handler->handle($request);

		static::assertEquals($expected->success, $result->success);
		static::assertEquals($expected->message, $result->message);
		static::assertEquals($expected->url, $result->url);
		static::assertEquals($expected->tweet, $result->tweet);
	}
}
