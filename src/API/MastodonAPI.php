<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API;

use Colorfield\Mastodon\MastodonAPI as ColorfieldMastodonAPI;
use Colorfield\Mastodon\MastodonOAuth;
use ricardoboss\WebhookTooter\ApiService;
use Throwable;

class MastodonAPI implements ApiService {
	private string $appName = "ricardoboss/webhook-tooter";

	private ?string $instanceUrl = null;

	private ?string $bearerToken = null;

	private ?string $clientId = null;

	private ?string $clientSecret = null;

	/**
	 * @codeCoverageIgnore
	 */
	public function setAppName(string $name): void {
		$this->appName = $name;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function setInstanceUrl(string $url): void {
		$this->instanceUrl = $url;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function setBearerToken(string $token): void {
		$this->bearerToken = $token;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function setClientId(string $clientId): void {
		$this->clientId = $clientId;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function setClientSecret(string $clientSecret): void {
		$this->clientSecret = $clientSecret;
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function getApi(): ColorfieldMastodonAPI {
		if ($this->instanceUrl === null) {
			throw new ApiException("No instance URL has been configured");
		}

		if ($this->clientId === null) {
			throw new ApiException("No clientId has been configured");
		}

		if ($this->clientSecret === null) {
			throw new ApiException("No clientId has been configured");
		}

		if ($this->bearerToken === null) {
			throw new ApiException("No bearerToken has been configured");
		}

		$oauth = new MastodonOAuth($this->appName, $this->instanceUrl);

		$config = $oauth->config;
		$config->setClientId($this->clientId);
		$config->setClientSecret($this->clientSecret);
		$config->setBearer($this->bearerToken);

		return new ColorfieldMastodonAPI($config);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function send(string $message): object {
		$api = $this->getApi();

		try {
			$result = $api->getResponse(
				'/statuses',
				'POST',
				[
					'status' => $message,
				]
			);
		} catch (Throwable $t) {
			throw new ApiException("An exception occurred while performing the API request", previous: $t);
		}

		if (isset($result['error'])) {
			throw new ApiException("The API returned an error: " . $result['error']);
		}

		return (object)$result;
	}

	public function getUrl(object $note): ?string {
		return $note->url;
	}
}
