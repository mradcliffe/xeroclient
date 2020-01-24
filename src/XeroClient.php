<?php

namespace Radcliffe\Xero;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Radcliffe\Xero\Exception\InvalidOptionsException;

class XeroClient extends Client implements XeroClientInterface
{
   /**
    * A list of valid tenant guids.
    *
    * @var string[]
    */
    protected $tenantIds = [];

    /**
     * @var
     */
    protected $refreshedToken = null;

    /**
     * {@inheritdoc}
     */
    public static function getValidUrls()
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
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        $options = isset($config['options']) ? $config['options'] : [];
        $scheme = isset($config['scheme']) ? $config['scheme'] : 'oauth1';
        $auth = $scheme === 'oauth1' ? 'oauth' : null;

        if (!isset($config['base_uri']) ||
            !$config['base_uri'] ||
            !$this->isValidUrl($config['base_uri'])) {
            throw new InvalidOptionsException('API URL is not valid.');
        }

        if ($scheme === 'oauth1') {
            // Backwards-compatible with oauth1.
            if (!isset($config['consumer_key']) || !$config['consumer_key']) {
                throw new InvalidOptionsException('Missing required parameter consumer_key');
            }

            if (!isset($config['consumer_secret']) || !$config['consumer_secret']) {
                throw new InvalidOptionsException('Missing required parameter consumer_secret');
            }

            if ($config['application'] === 'private') {
                $config['token'] = $config['consumer_key'];
            }

            if ($config['application'] === 'private') {
                $config['token_secret'] = $config['consumer_secret'];
            }

            if ($config['application'] === 'private' &&
                (!isset($config['private_key']) || !$this->isValidPrivateKey($config['private_key']))
            ) {
                throw new InvalidOptionsException('Missing required parameter private_key');
            }

            if ($config['application'] === 'private') {
                $middleware = $this->getPrivateApplicationMiddleware($config);
            } else {
                $middleware = $this->getPublicApplicationMiddleware($config);
            }
        } elseif ($scheme === 'oauth2') {
            // Use OAuth2 work flow.
            if (!isset($config['auth_token'])) {
                throw new InvalidOptionsException('Missing required parameter auth_token');
            }
            $options['headers']['Authorization'] = 'Bearer ' . $config['auth_token'];
        } else {
            throw new InvalidOptionsException('Invalid scheme provided');
        }

        if (isset($config['handler']) && is_a($config['handler'], '\GuzzleHttp\HandlerStack')) {
            $stack = $config['handler'];
        } else {
            $stack = HandlerStack::create();
        }

        if (isset($middleware)) {
            $stack->push($middleware);
        }

        parent::__construct($options + [
            'base_uri' => $config['base_uri'],
            'handler' => $stack,
            'auth' => $auth,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function isValidUrl($base_uri)
    {
        return in_array($base_uri, $this->getValidUrls()) || strpos($base_uri, 'https://api.xero.com/oauth') === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidPrivateKey($filename)
    {
        if ($filename && realpath($filename) && !is_dir($filename) && is_readable($filename)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $options
     *   The options passed into the constructor.
     *
     * @return \GuzzleHttp\Subscriber\Oauth\Oauth1
     *   OAuth1 middleware.
     *
     * @deprecated Deprecated since 0.2.0.
     */
    protected function getPublicApplicationMiddleware($options)
    {
        $oauth_options = [
            'consumer_key' => $options['consumer_key'],
            'consumer_secret' => $options['consumer_secret'],
        ];

        if (isset($options['token'])) {
            $oauth_options['token'] = $options['token'];
        }

        if (isset($options['token_secret'])) {
            $oauth_options['token_secret'] = $options['token_secret'];
        }

        if (isset($options['callback'])) {
            $oauth_options['callback'] = $options['callback'];
        }

        if (isset($options['verifier'])) {
            $oauth_options['verifier'] = $options['verifier'];
        }

        return new Oauth1($oauth_options);
    }

    /**
     * @param array $options
     *   The options passed into the constructor.
     *
     * @return \GuzzleHttp\Subscriber\Oauth\Oauth1
     *   OAuth1 middleware.
     *
     * @deprecated Deprecated since 0.2.0
     */
    protected function getPrivateApplicationMiddleware($options)
    {
        return new Oauth1([
            'consumer_key' => $options['consumer_key'],
            'consumer_secret' => $options['consumer_secret'],
            'token' => $options['token'],
            'token_secret' => $options['token_secret'],
            'private_key_file' => $options['private_key'],
            'private_key_passphrase' => null,
            'signature_method' => Oauth1::SIGNATURE_METHOD_RSA,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getRequestToken($consumer_key, $consumer_secret, $options = [])
    {
        $config = [
                'base_uri' => 'https://api.xero.com/oauth/',
                'consumer_key' => $consumer_key,
                'consumer_secret' => $consumer_secret,
                'application' => 'public',
            ] + $options;
        $client = new static($config);

        $tokens = [];
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $client->post('/RequestToken');
        $pairs = explode('&', $response->getBody()->getContents());
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = urldecode($split[0]);
            $tokens[$parameter] = isset($split[1]) ? urldecode($split[1]) : '';
        }
        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAccessToken(
        $consumer_key,
        $consumer_secret,
        $token,
        $token_secret,
        $verifier,
        $options = []
    ) {
        $config = [
                'base_uri' => 'https://api.xero.com/oauth/',
                'consumer_key' => $consumer_key,
                'consumer_secret' => $consumer_secret,
                'token' => $token,
                'token_secret' => $token_secret,
                'verifier' => $verifier,
                'application' => 'public',
            ] + $options;

        $client = new static($config);

        $tokens = [];
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $client->post('/AccessToken');
        $pairs = explode('&', $response->getBody()->getContents());
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = urldecode($split[0]);
            $tokens[$parameter] = isset($split[1]) ? urldecode($split[1]) : '';
        }
        return $tokens;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public static function createFromToken(
        $id,
        $secret,
        $token,
        $grant = null,
        $api = 'accounting',
        array $options = [],
        array $collaborators = []
    ) {
        if ($grant !== null) {
            // Fetch a new access token from a refresh token.
            $provider = new XeroProvider([
                'clientId' => $id,
                'clientSecret' => $secret,
                'scopes' => XeroProvider::getValidScopes($api),
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
        $instance = new static($options + [
            'scheme' => 'oauth2',
            'auth_token' => $token,
        ]);

        $response = $instance->get('https://api.xero.com/connections');
        $instance->tenantIds = json_decode($response->getBody()->getContents(), true);

        if (isset($refreshedToken)) {
            $instance->refreshedToken = $refreshedToken;
        }

        return $instance;
    }

    /**
     * Access tokens refreshed when creating an instance from a refresh token.
     *
     * @return \League\OAuth2\Client\Token\AccessTokenInterface|null
     */
    public function getRefreshedToken()
    {
        return $this->refreshedToken;
    }

    /**
     * The tenant guids accessible by this client.
     *
     * @return string[]
     */
    public function getTenantIds()
    {
        return $this->tenantIds;
    }
}
