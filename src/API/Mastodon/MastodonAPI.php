<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTooter\API\Mastodon;

use Colorfield\Mastodon\MastodonAPI as ColorfieldMastodonAPI;
use Colorfield\Mastodon\MastodonOAuth;
use ricardoboss\WebhookTooter\ApiService;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class MastodonAPI implements ApiService {
	private string $appName = "ricardoboss/webhook-tooter";

	private ?string $instanceUrl = null;

	private ?string $bearerToken = null;

	private ?string $clientId = null;

	private ?string $clientSecret = null;

	public function setAppName(string $name): void {
		$this->appName = $name;
	}

	public function setInstanceUrl(string $url): void {
		$this->instanceUrl = $url;
	}

	public function setBearerToken(string $token): void {
		$this->bearerToken = $token;
	}

	public function setClientId(string $clientId): void {
		$this->clientId = $clientId;
	}

	public function setClientSecret(string $clientSecret): void {
		$this->clientSecret = $clientSecret;
	}

	private function getApi(): ColorfieldMastodonAPI {
		if ($this->instanceUrl === null) {
			throw new MastodonConfigurationException("No instance URL has been configured");
		}

		if ($this->clientId === null) {
			throw new MastodonConfigurationException("No clientId has been configured");
		}

		if ($this->clientSecret === null) {
			throw new MastodonConfigurationException("No clientId has been configured");
		}

		if ($this->bearerToken === null) {
			throw new MastodonConfigurationException("No bearerToken has been configured");
		}

		$oauth = new MastodonOAuth($this->appName, $this->instanceUrl);

		$config = $oauth->config;
		$config->setClientId($this->clientId);
		$config->setClientSecret($this->clientSecret);
		$config->setBearer($this->bearerToken);

		return new ColorfieldMastodonAPI($config);
	}

	public function send(string $message): MastodonApiResult {
		$api = $this->getApi();

		try {
			$statusResponse = $api->getResponse(
				'/statuses',
				'POST',
				[
					'status' => $message,
				]
			);
		} catch (Throwable $t) {
			throw new MastodonApiException("An exception occurred while performing the API request", previous: $t);
		}

		if (isset($statusResponse['error'])) {
			throw new MastodonApiException("The API returned an error: " . $statusResponse['error']);
		}

		return new MastodonApiResult($statusResponse);
	}
}
