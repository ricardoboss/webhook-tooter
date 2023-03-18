# webhook-tooter

This package aims to provide simple interfaces to implement a webhook-based post publishing system.

This can, for example, be used to tweet or toot a release notification when a new release is published to a GitHub
repository.

## Installation

```bash
composer require ricardoboss/webhook-tooter
```

## Usage

```php
<?php
use ricardoboss\WebhookTooter\WebhookTooterConfig;
use ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterRenderer;
use ricardoboss\WebhookTooter\Simple\SimpleWebhookTooterTemplateLocator;
use ricardoboss\WebhookTooter\WebhookTooterHandler;
use ricardoboss\WebhookTooter\API\BirdElephantTwitterAPI;
use ricardoboss\WebhookTooter\API\MastodonAPI;
use ricardoboss\WebhookTooter\WebhookTooterAPI;

// 1. Create a config object
// you can also pass \Stringable objects instead of strings
$config = new WebhookTooterConfig(
    'webhook_url',   // nullable; null will ignore the path in the request
    'webhook_secret' // nullable; null will disable signature verification
);

// 2. Create an instance of WebhookTooterRenderer
// either use your own renderer or use the simple renderer
$renderer = new SimpleWebhookTooterRenderer();

// 3. Create a template locator instance
// the simple locator looks for files in the given directory, and the given extension (name is passed to the getMatchingTemplate method)
$locator = new SimpleWebhookTooterTemplateLocator(__DIR__ . '/templates', '.md');

// 4a. Create a Twitter API client
$twitter = new BirdElephantTwitterAPI();
$twitter->setCredentials([
    'bearer_token' => xxxxxx,     // OAuth 2.0 Bearer Token requests
    'consumer_key' => xxxxxx,     // identifies your app, always needed
    'consumer_secret' => xxxxxx,  // app secret, always needed
    'token_identifier' => xxxxxx, // OAuth 1.0a User Context requests
    'token_secret' => xxxxxx,     // OAuth 1.0a User Context requests
]);

// 4b. Alternatively, create a Mastodon API Client
$mastodon = new MastodonAPI();
$mastodon->setInstanceUrl('phpc.social');
$mastodon->setClientId(xxxxx);     // Get your client ID, secret and token
$mastodon->setClientSecret(xxxxx); // by creating an application in the developer
$mastodon->setBearerToken(xxxxx);  // options on your mastodon instance.

// 4c. You can supply any API you want as all it needs to implement is the WebhookTooterAPI interface
$custom = new class implements WebhookTooterAPI {
    public function send(string $message): object {
        // TODO: send the given message to an API
    }

    public function getUrl(object $note): ?string {
        // TODO: build a URL to the send message using the returned object
    }
}

// 5. Create a WebhookTooterHandler instance
$handler = new WebhookTooterHandler($config, $renderer, $locator, $twitter);

// 6. Get a PSR-7 request object
$request = /* get your request implementation */;

// 7. Handle the request (sends a rendered message)
$result = $handler->handle($request);
```

The `$result` variable holds a `WebhookTooterResult` instance.
The result has the following properties:

- `$result->success`: `true` if the message was sent successfully, `false` otherwise
- `$result->message`: an error message if the message was not sent successfully
- `$result->url`: a URL to the note
- `$result->note`: the object returned from the API

## Credits

These nice people provide neat PHP interfaces for the Twitter and Mastodon APIs!

Thanks to [danieldevine](https://github.com/danieldevine) for creating [BirdElephant](https://github.com/danieldevine/bird-elephant)!

Thanks to [colorfield](https://github.com/colorfield) for creating [mastodon-api-php](https://github.com/colorfield/mastodon-api-php) and [afterlogic](https://github.com/afterlogic) for maintaining a [fork](https://github.com/afterlogic/mastodon-api-php) of it!

## License

This project is licensed under the MIT license. Read more about it [here](./LICENSE).
