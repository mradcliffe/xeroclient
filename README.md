# Xero Client

xeroclient is a PHP library that extends Guzzle to provide integration with the [Xero API](https://developer.xero.com). It is primarily used as an API layer for your own project. It supports connecting to the Accounting API, Payroll API and File API URLs as either a private, public or partner application although implementation and storage of OAuth1 configuration is up to the implementing software. xeroclient aims to abide by the following criteria in regard to Xero integration:

1. Abides by the PSR-2 standard.
2. Uses contemporary PHP libraries such as Guzzle.
3. Is lightweight and pluggable into a variety of frameworks that do normalization and data modeling their own way.
4. Is testable.

Ultimately it is up to the software that uses xeroclient to deal with [serialization](http://symfony.com/doc/current/components/serializer.html), data modeling, OAuth2 redirect work flow, and configuration or content management.

[![Build Status](https://travis-ci.org/mradcliffe/xeroclient.svg?branch=master)](https://travis-ci.org/mradcliffe/xeroclient)

**Please see [CONTRIBUTING](./CONTRIBUTING.md) for more information about contributing to this project including Code of Conduct, Accountability, and How to get started.**

## Dependencies

* PHP 7.0 or greater
* (Deprecated) [guzzlehttp/oauth-subscriber](https://packagist.org/packages/guzzlehttp/oauth-subscriber)
* [league/oauth2-client](https://packagist.org/packages/league/oauth2-client)
* [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)

## Usage

### Request an access token from Xero API using OAuth2.

```php
// Create a new provider.
$provider = new \Radcliffe\Xero\XeroProvider([
    'clientId' => 'my consumer key',
    'clientSecret' => 'my consumer secret',
    'redirectUri' => 'https://example.com/path/to/my/xero/callback',
    // This will always request offline_access.
    'scopes' => \Radcliffe\Xero\XeroProvider::getValidScopes('accounting'),
]);

// Gets the URL to go to get an authorization code from Xero.
$url = $provider->getAuthorizationUrl();
```

### Create a guzzle client from an authorization code (see above)

```php
$client = \Radcliffe\Xero\XeroClient::createFromToken('my consumer key', 'my consumer secret', $code, 'authorization_code', 'accounting');
// Store the access token for the next 30 minutes or so if making additional requests.
$tokens = $client->getRefreshedToken();
```

### Create a guzzle client with an access token

```php
$client = \Radcliffe\Xero\XeroClient::createFromToken('my consumer key', 'my consumer secret', 'my access token');
```

### Create a guzzle client with a refresh token

Access tokens expire after 30 minutes so you can create a new client with a stored refresh token too.

```php
$client = \Radcliffe\Xero\XeroClient::createFromToken('my consumer key', 'my consumer secret', 'my request token', 'request_token', 'accounting');
// Get the refreshed tokens and store it somewhere.
$tokens = $client->getRefreshedToken();
```

### Use the client instance to make requests

```php
try {
	$options = [
		'query' => ['where' => 'Name.StartsWith("John")'],
		'headers' => ['Accept' => 'application/json'],
	];
	$response = $client->get('Accounts', $options);

	// Or use something like Symfony Serializer component.
	$accounts = json_decode($response->getBody()->getContents());
} catch (\GuzzleHttp\Exception\ClientException $e) {
	echo 'Request failed because of ' . $e->getResponse()->getStatusCode();
}

```

### Use with a legacy OAuth1 application

Please see the 0.2 branch and versions < 0.3.0.

### Xero Helper Trait

The XeroHelperTrait provides some useful methods to attach to your classes for dealing with various Xero API query parameters and headers.

## License

* This software is primarily licensed under the MIT license.
* Exception is granted to use the software under the GPLv2 license.

## Alternate libraries

* [xero-php-oauth2](https://github.com/XeroAPI/xero-php-oauth2) provides an auto-generated SDK for accessing the Xero API that injects Guzzle into each model.
* [xero-php](https://github.com/calcinai/xero-php) provides an all-in-one solution based on data model assumptions using Curl for PHP 5.3 applications.
* [PHP-Xero](https://github.com/drpitman/PHP-Xero) provides OAuth1 and Xero classes in the global namespace. Horribly outdated and should not be used. I have a [fork](https://github.com/mradcliffe/PHP-Xero).
* [XeroBundle](https://github.com/james75/XeroBundle) provides a Symfony2 Bundle that is the inspiration for this lightweight library. It is possible to wrap your own factory class to ignore the Symfony2 bundle configuration. Currently broken (my fault) unless you use [my fork](https://github.com/mradcliffe/XeroBundle).
* [XeroOAuth-PHP](https://github.com/XeroAPI/XeroOAuth-PHP) provides OAuth1 and Xero classes in the global namespace and maintained by the Xero API team for older PHP 5.3 applications.
* [Xero API](https://drupal.org/project/xero) provides a Drupal module that integrates with the Xero API. In Drupal 8 this depends on this library or XeroBundle above.
* There are numerous other libraries providing custom code.

## Affiliation

This library is not affiliated with Xero Limited.
