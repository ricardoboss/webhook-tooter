<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

use JsonException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Mockery as M;
use ricardoboss\WebhookTooter\API\Twitter\TwitterApiResponse;
use ricardoboss\WebhookTooter\Simple\SimpleTemplateRenderer;
use ricardoboss\WebhookTooter\Simple\SimpleTemplateLocator;
use stdClass;
use Stringable;

/**
 * @covers \ricardoboss\WebhookTooter\RequestHandler
 * @covers \ricardoboss\WebhookTooter\WebhookConfig
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleTemplateRenderer
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleTemplateLocator
 * @covers \ricardoboss\WebhookTooter\Simple\SimpleTemplate
 * @covers \ricardoboss\WebhookTooter\RequestHandlerResult
 *
 * @internal
 */
class RequestHandlerTest extends TestCase
{
	/**
	 * @throws JsonException
	 */
	public function requestProvider(): iterable
	{
		$factory = new Psr17Factory();

		$config = new WebhookConfig('/webhook', 'secret');
		$renderer = new SimpleTemplateRenderer();
		$templateLocator = new SimpleTemplateLocator(__DIR__ . '/templates');
		$twitter = M::mock(ApiService::class);

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
		$testTweetResult = new TwitterApiResponse($testTweetObject);

		$baseRequest = $factory
			->createRequest('POST', 'https://example.com' . $config->path)
			->withHeader('Content-Type', 'application/json')
			->withBody($factory->createStream($testDataJson))
			->withHeader(RequestHandler::SignatureHeader, RequestHandler::SignatureAlgorithm . '=' . hash_hmac(RequestHandler::SignatureAlgorithm, $testDataJson, $config->secret));
		$successResult = new RequestHandlerResult(true, null, $testTweetResult);

		$testDataWithTemplateData = [
			'event' => 'data',
			'data' => 'testdata',
		];
		$testDataWithTemplateDataJson = json_encode($testDataWithTemplateData, JSON_THROW_ON_ERROR);
		$baseRequestWithData = $baseRequest
			->withBody($factory->createStream($testDataWithTemplateDataJson))
			->withHeader(RequestHandler::SignatureHeader, RequestHandler::SignatureAlgorithm . '=' . hash_hmac(RequestHandler::SignatureAlgorithm, $testDataWithTemplateDataJson, $config->secret))
		;

		$twitter
			->expects('send')
			->with("This is a test template.\n")
			->andReturns($testTweetResult)
		;
		$twitter
			->expects('send')
			->with("Data: " . $testDataWithTemplateData['data'] . "\n")
			->andReturns($testTweetResult)
		;

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $baseRequest,
			'expected' => $successResult,
		];

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $baseRequestWithData,
			'expected' => $successResult,
		];

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $baseRequest,
			'expected' => $successResult,
		];

		$invalidMethodRequest = $baseRequest->withMethod('GET');
		$invalidMethodResult = new RequestHandlerResult(false, 'Invalid request method: GET', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $invalidMethodRequest,
			'expected' => $invalidMethodResult,
		];

		$invalidPathRequest = $baseRequest->withUri(new Uri('https://example.com/not-the-webhook-path'));
		$invalidPathResult = new RequestHandlerResult(false, 'Invalid request path: /not-the-webhook-path', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $invalidPathRequest,
			'expected' => $invalidPathResult,
		];

		$invalidSecretRequest = $baseRequest->withHeader(RequestHandler::SignatureHeader, 'not-the-signature');
		$invalidSecretResult = new RequestHandlerResult(false, 'Invalid request signature', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $invalidSecretRequest,
			'expected' => $invalidSecretResult,
		];

		$invalidContentTypeRequest = $baseRequest->withHeader('Content-Type', 'application/x-www-form-urlencoded');
		$invalidContentTypeResult = new RequestHandlerResult(false, 'Invalid request content type: application/x-www-form-urlencoded', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $invalidContentTypeRequest,
			'expected' => $invalidContentTypeResult,
		];

		$invalidContentRequest = $baseRequest
			->withBody($factory->createStream('invalid-json'))
			->withHeader(RequestHandler::SignatureHeader, RequestHandler::SignatureAlgorithm . '=' . hash_hmac(RequestHandler::SignatureAlgorithm, 'invalid-json', $config->secret))
		;
		$invalidContentResult = new RequestHandlerResult(false, 'Invalid request payload: Syntax error', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $invalidContentRequest,
			'expected' => $invalidContentResult,
		];

		$configWithoutSecret = new WebhookConfig('/webhook');
		$baseRequestWithoutSignature = $baseRequest->withoutHeader(RequestHandler::SignatureHeader);

		yield [
			'config' => $configWithoutSecret,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $baseRequestWithoutSignature,
			'expected' => $successResult,
		];

		$configWithStringable = new WebhookConfig(new class implements Stringable {
			public function __toString(): string
			{
				return '/webhook';
			}
		});

		yield [
			'config' => $configWithStringable,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'api' => $twitter,
			'request' => $baseRequestWithoutSignature,
			'expected' => $successResult,
		];
	}

	/**
	 * @dataProvider requestProvider
	 */
	public function testHandle(
		WebhookConfig $config,
		TemplateRenderer $renderer,
		TemplateLocator $templateLocator,
		ApiService $api,
		RequestInterface $request,
		RequestHandlerResult $expected,
	): void
	{
		$request->getBody()->rewind();

		$handler = new RequestHandler($config, $renderer, $templateLocator, $api);
		$result = $handler->handle($request);

		static::assertEquals($expected->success, $result->success);
		static::assertEquals($expected->message, $result->message);
		static::assertEquals($expected->result, $result->result);
	}
}
