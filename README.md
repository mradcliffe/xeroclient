# Xero Client

xeroclient is a PHP library that extends Guzzle to provide integration with the [Xero API](https://developer.xero.com). It is primarily used as an API layer for your own project. It supports connecting to the Accounting API, Payroll API and File API URLs as either a private, public or partner application although implementation and storage of OAuth1 configuration is up to the implementing software. xeroclient aims to abide by the following criteria in regard to Xero integration:

1. Abides by the PSR-2 standard.
2. Uses contemporary PHP libraries such as Guzzle.
3. Is lightweight and pluggable into a variety of frameworks that do normalization and data modeling their own way.
4. Is testable.

Ultimately it is up to the software that uses xeroclient to deal with [serialization](http://symfony.com/doc/current/components/serializer.html), data modeling, and configuration or content management.

[![Build Status](https://travis-ci.org/mradcliffe/xeroclient.svg?branch=master)](https://travis-ci.org/mradcliffe/xeroclient)

## Dependencies

* PHP 5.6 or greater
* [guzzlehttp/oauth-subscriber](https://packagist.org/packages/guzzlehttp/oauth-subscriber)
* [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)

## Usage

### Use with a private application

```php

$config = [
	// Note the trailing slash.
	'base_uri' => 'https://api.xero.com/api.xro/2.0/',
	'application' => 'private',
	'consumer_key' => '',
	'consumer_secret' => '',
	// This path must be accessible by file_get_contents(), which does support
	// registered stream wrappers, but php://memory will not work.
	'private_key' => '/path/to/private/application/key',
];

$client = new \Radcliffe\Xero\XeroClient($config);

try {
	$options = [
		'query' => ['where' => 'Name.StartsWith("John")'],
		'headers' => ['Accept' => 'application/json'],
	];
	$response = $client->get('Accounts', $options);

	// Or use something like Symfony Serializer component.
	$accounts = json_decode($response->getBody()->getContents());
} catch (\GuzzleHttp\Exception\RequestException $e) {
	echo 'Request failed because of ' . $e->getResponse()->getStatusCode();
}

```

### Use with a public or partner application

* Assumes that your web site will handle routing to accept oauth callbacks from Xero.
* xeroclient provides some static methods on the XeroClient class to make request token and access token requests.
	* `$requestTokens = XeroClient::getRequestToken($consumer_key, $consumer_secret, ['application' => 'public']);`
	* Assumes that your web site will handle routing and storage for OAuth1 authorization process.
	* `$accessTokens = XeroClient::getAccessToken($consumer_key, $consumer_secret, $token, $token_secret, $verifier, ['application' => 'public']);`
	* Then you can make requests with the configuration below:

```
$config = [
	// Note the trailing slash.
	'base_uri' => 'https://api.xero.com/api.xro/2.0/',
	'application' => 'public',
	'consumer_key' => $consumer_key,
	'consumer_secret' => $consumer_secret,
	'token' => $accessTokens['oauth_token'],
	'token_secret' => $accessTokens['oauth_token_secret'],
	// This path must be accessible by file_get_contents(), which does support
	// registered stream wrappers, but php://memory will not work.
	'private_key' => '/path/to/public/application/key',
];
$client = new XeroClient($config);
$response = $client->get('Accounts');
```

### Xero Helper Trait

The XeroHelperTrait provides some useful methods to attach to your classes for dealing with various Xero API query parameters and headers.

## License

* This software is primarily licensed under the MIT license.
* Exception is granted to use the software under the GPLv2 license.

## Other libraries

* [xero-php](https://github.com/calcinai/xero-php) provides an all-in-one solution based on data model assumptions using Curl for PHP 5.3 applications.
* [PHP-Xero](https://github.com/drpitman/PHP-Xero) provides OAuth1 and Xero classes in the global namespace. Horribly outdated and should not be used. I have a [fork](https://github.com/mradcliffe/PHP-Xero).
* [XeroBundle](https://github.com/james75/XeroBundle) provides a Symfony2 Bundle that is the inspiration for this lightweight library. It is possible to wrap your own factory class to ignore the Symfony2 bundle configuration. Currently broken (my fault) unless you use [my fork](https://github.com/mradcliffe/XeroBundle).
* [XeroOAuth-PHP](https://github.com/XeroAPI/XeroOAuth-PHP) provides OAuth1 and Xero classes in the global namespace and maintained by the Xero API team for older PHP 5.3 applications.
* [Xero API](https://drupal.org/project/xero) provides a Drupal module that integrates with the Xero API. In Drupal 8 this depends on this library or XeroBundle above.
* There are numerous other libraries providing custom code.

## Affiliation

This library is not affiliated with Xero.
