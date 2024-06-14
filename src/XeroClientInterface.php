<?php

namespace Radcliffe\Xero;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface XeroClientInterface
{
    /**
     * Get a list of valid API URLs.
     *
     * @return string[]
     */
    public static function getValidUrls(): array;

    /**
     * Check the URL.
     *
     * @param string $base_uri
     *
     * @return bool
     *   TRUE if the base uri is valid.
     *
     * @deprecated in 0.5.0 and removed in 0.6.0. The check of base_uri is now
     *             internal to XeroClient.
     *
     * @see XeroClient::createFromConfig().
     */
    public function isValidUrl(string $base_uri): bool;

    /**
     * Get connections authorized by the user.
     *
     * @return array<int,mixed>
     *   An indexed array of connections authorized by the user.
     */
    public function getConnections(): array;

    /**
     * Create client from an existing token or code.
     *
     * Regardless of the parameters, the returned object will be a Guzzle Client
     * instance with an Authorization header using the access token for the application.
     *
     * There are three ways to do this:
     *    1. Directly with an existing access token.
     *    2. Using an authorization code retrieved within 15 minutes to get an access token.
     *    3. Using a refresh token when an access token has expired after 30 minutes.
     *
     * This will create two side effects:
     *    1. An additional request will always be made to confirm the tenant ids allowed by
     *       the access token and stored in $this->tenantIds.
     *    2. If a code or refresh token is used, then the new access token and related
     *       information will be stored in $this->refreshedToken.
     *
     * @param string $id
     *   The Oauth2 client id.
     * @param string $secret
     *   The Oauth2 client secret.
     * @param string $token
     *   An access token, refresh token, or authorization code.
     * @param string|null $grant
     *   An optional grant type when refreshing or getting a new access token.
     *     - refresh_token: the provided token is a refresh token.
     *     - authorization_code: the provided token is an authorization code.
     * @param string $api
     *   The Xero API to scope to which is one of the following: accounting,
     *   payroll_COUNTRYCODE, files, assets, projects, restricted, or openid.
     * @param array<string,string> $options
     *   Any additional options to pass to the constructor.
     * @param array<string,mixed> $collaborators
     *   Collaborator options to pass through to the provider initialize method.
     * @param string $redirectUri
     *   The redirect uri corresponding to the Xero application.
     *
     * @see \League\OAuth2\Client\Provider\AbstractProvider::__construct()
     *
     * @return static
     */
    public static function createFromToken(
        string $id,
        string $secret,
        string $token,
        string $grant = null,
        string $api = 'accounting',
        array $options = [],
        array $collaborators = [],
        string $redirectUri = ''
    ): static;

    /**
     * Creates an instance of XeroClient with guzzle configured from options.
     *
     * @param array<string,mixed> $config
     *   The guzzle options.
     * @param array<string,mixed> $options
     *   The XeroClient Options:
     *     - auth_token: the access or refresh token.
     *     - tenant: an optional tenant id.
     *
     * @return static
     *
     * @see \GuzzleHttp\Client::__construct().
     */
    public static function createFromConfig(array $config, array $options = []): static;

    /**
     * Makes a request to the Xero API.
     *
     * @param string $method
     *   The request methad.
     * @param string|\Psr\Http\Message\UriInterface $uri
     *   The endpoint path.
     * @param array<string,mixed> $options
     *   Options to pass to the http client.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   The response from the http client.
     */
    public function request(string $method, string|UriInterface $uri = '', array $options = []): ResponseInterface;

    /**
     * Makes a GET request to the Xero API endpoint.
     *
     * @param string|\Psr\Http\Message\UriInterface $uri
     *    The endpoint path.
     * @param array<string,mixed> $options
     *    Options to pass to the http client.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *    The response from the http client.
     *
     * @deprecated in 0.5.0 and removed in 0.6.0. Use the request method.
     */
    public function get(string|UriInterface $uri = '', array $options = []): ResponseInterface;

    /**
     * Makes a POST request to the Xero API endpoint.
     *
     * @param string|\Psr\Http\Message\UriInterface $uri
     * The endpoint path.
     * @param array<string,mixed> $options
     * Options to pass to the http client.
     *
     * @return \Psr\Http\Message\ResponseInterface
     * The response from the http client.
     *
     * @deprecated in 0.5.0 and removed in 0.6.0. Use the request method.
     */
    public function post(string|UriInterface $uri = '', array $options = []): ResponseInterface;

    /**
     * Makes a PUT request to the Xero API endpoint.
     *
     * @param string|\Psr\Http\Message\UriInterface $uri
     * The endpoint path.
     * @param array<string,mixed> $options
     * Options to pass to the http client.
     *
     * @return \Psr\Http\Message\ResponseInterface
     * The response from the http client.
     *
     * @deprecated in 0.5.0 and removed in 0.6.0. Use the request method.
     */
    public function put(string|UriInterface $uri = '', array $options = []): ResponseInterface;
}
