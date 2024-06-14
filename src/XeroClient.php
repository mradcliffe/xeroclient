<?php

namespace Radcliffe\Xero;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Radcliffe\Xero\Exception\InvalidOptionsException;

class XeroClient implements XeroClientInterface
{
   /**
    * A list of valid tenant guids.
    *
    * @var string[]
    */
    protected array $tenantIds = [];

    /**
     * @var \League\OAuth2\Client\Token\AccessTokenInterface|null
     */
    protected ?AccessTokenInterface $refreshedToken = null;

    /**
     * @var \GuzzleHttp\ClientInterface|null
     */
    protected ?ClientInterface $client = null;

    /**
     * {@inheritdoc}
     */
    public static function getValidUrls(): array
    {
        return [
            'https://identity.xero.com/connect/token',
            'https://api.xero.com/connections',
            'https://api.xero.com/api.xro/2.0/',
            'https://api.xero.com/payroll.xro/1.0/',
            'https://api.xero.com/assets.xro/1.0/',
            'https://api.xero.com/files.xro/1.0/',
        ];
    }

    /**
     * Initialization method.
     *
     * @param \GuzzleHttp\ClientInterface $client
     *   The guzzle client that is already fully configured for use with Xero.
     *   This method of initializing XeroClient should never be used without a
     *   static method because the guzzle developers mark useful methods as
     *   derpecated for no good reason.
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidUrl($base_uri): bool
    {
        return in_array($base_uri, self::getValidUrls());
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections(): array
    {
        try {
            $response = $this->request('GET', 'https://api.xero.com/connections', [
              'Content-Type' => 'application/json',
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->getCode() >= 400) {
                throw $e;
            }
            return [];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException
     */
    public static function createFromConfig(array $config): static
    {
        if (!isset($config['base_uri']) ||
          !$config['base_uri'] ||
          !in_array($config['base_uri'], self::getValidUrls())) {
            throw new InvalidOptionsException('API URL is not valid.');
        }

        // Use OAuth2 work flow.
        if (!isset($config['auth_token'])) {
            throw new InvalidOptionsException('Missing required parameter auth_token');
        }
        $options['headers']['Authorization'] = 'Bearer ' . $config['auth_token'];

        if (isset($config['tenant'])) {
            $options['headers']['xero-tenant-id'] = $config['tenant'];
        }

        if (isset($config['handler']) && is_a($config['handler'], '\GuzzleHttp\HandlerStack')) {
            $stack = $config['handler'];
        } else {
            $stack = HandlerStack::create();
        }

        $client = new Client($config + [
            'handler' => $stack,
        ]);
        return new static($client);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException
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
    ): static {
        if ($grant !== null) {
            // Fetch a new access token from a refresh token.
            $provider = new XeroProvider([
                'clientId' => $id,
                'clientSecret' => $secret,
                'scopes' => XeroProvider::getValidScopes($api),
                'redirectUri' => $redirectUri,
            ], $collaborators);
            $token_options = [];
            if ($grant === 'refresh_token') {
                $token_options['refresh_token'] = $token;
            } elseif ($grant === 'authorization_code') {
                $token_options['code'] = $token;
            }

            $refreshedToken = $provider->getAccessToken($grant, $token_options);
            $token = $refreshedToken->getToken();
        }

        if (!isset($options['base_uri'])) {
            $options['base_uri'] = 'https://api.xero.com/api.xro/2.0/';
        }

        // Create a new static instance.
        $instance = self::createFromConfig($options + [
            'auth_token' => $token,
        ]);

        $instance->tenantIds = $instance->getConnections();

        if (isset($refreshedToken)) {
            $instance->refreshedToken = $refreshedToken;
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, UriInterface|string $uri = '', array $options = []): ResponseInterface
    {
        return $this->client->request(strtoupper($method), $uri, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(UriInterface|string $uri = '', array $options = []): ResponseInterface
    {
        return $this->request('GET', $uri, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(UriInterface|string $uri = '', array $options = []): ResponseInterface
    {
        return $this->request('POST', $uri, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put(UriInterface|string $uri = '', array $options = []): ResponseInterface
    {
        return $this->request('PUT', $uri, $options);
    }

    /**
     * Access tokens refreshed when creating an instance from a refresh token.
     *
     * @return \League\OAuth2\Client\Token\AccessTokenInterface|null
     */
    public function getRefreshedToken(): ?AccessTokenInterface
    {
        return $this->refreshedToken;
    }

    /**
     * The tenant guids accessible by this client.
     *
     * @return string[]
     */
    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }
}
