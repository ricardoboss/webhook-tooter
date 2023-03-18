<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter;

use JsonException;
use Psr\Http\Message\RequestInterface;

class RequestHandler {
	public const SignatureHeader = 'X-Hub-Signature-256';
	public const SignatureAlgorithm = 'sha256';

	public function __construct(
		private readonly WebhookConfig $config,
		private readonly TemplateRenderer $renderer,
		private readonly TemplateLocator $templateLocator,
		private readonly ApiService $api,
	) {}

	public function handle(RequestInterface $request): RequestHandlerResult {
		$result = $this->verifyRequestHeaders($request);
		if ($result !== null) {
			return $result;
		}

		$body = $request->getBody()->getContents();

		if (!$this->verifySignature($request, $body)) {
			return RequestHandlerResult::failure('Invalid request signature');
		}

		try {
			$payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return RequestHandlerResult::failure("Invalid request payload: " . $e->getMessage());
		}

		$renderedTemplate = $this->renderTemplate($payload);
		$note = $this->api->send($renderedTemplate);
		$url = $this->api->getUrl($note);

		return RequestHandlerResult::success($url, $note);
	}

	private function verifyRequestHeaders(RequestInterface $request): ?RequestHandlerResult {
		$method = $request->getMethod();
		if ($method !== 'POST') {
			return RequestHandlerResult::failure("Invalid request method: $method");
		}

		if ($this->config->path !== null) {
			$webhookPath = (string) $this->config->path;
			$actualPath = $request->getUri()->getPath();
			if ($actualPath !== $webhookPath) {
				return RequestHandlerResult::failure("Invalid request path: $actualPath");
			}
		}

		$contentType = $request->getHeaderLine('Content-Type');
		if ($contentType !== 'application/json') {
			return RequestHandlerResult::failure("Invalid request content type: $contentType");
		}

		return null;
	}

	private function verifySignature(RequestInterface $request, string $body): bool {
		if ($this->config->secret === null) {
			return true;
		}

		$signature = $request->getHeaderLine(self::SignatureHeader);
		if (!str_starts_with($signature, self::SignatureAlgorithm . '=')) {
			return false;
		}

		$signature = substr($signature, strlen(self::SignatureAlgorithm) + 1);

		$hash = hash_hmac(self::SignatureAlgorithm, $body, (string) $this->config->secret);

		return hash_equals($hash, $signature);
	}

	private function renderTemplate(array $data): string {
		$template = $this->templateLocator->getMatchingTemplate($data) ?? $this->templateLocator->getDefaultTemplate();

		return $this->renderer->render($template, $data);
	}
}
