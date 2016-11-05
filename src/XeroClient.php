<?php

namespace Radcliffe\Xero;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Radcliffe\Xero\Exception\InvalidOptionsException;

class XeroClient extends Client implements XeroClientInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getValidUrls()
    {
        return [
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
        if (!isset($config['base_uri']) ||
            !$config['base_uri'] ||
            !$this->isValidUrl($config['base_uri'])) {
            throw new InvalidOptionsException('API URL is not valid.');
        }

        if (!isset($config['consumer_key']) || !$config['consumer_key']) {
            throw new InvalidOptionsException('Consumer key not found.');
        }

        if (!isset($config['consumer_secret']) || !$config['consumer_secret']) {
            throw new InvalidOptionsException('Consumer secret not found.');
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
            throw new InvalidOptionsException('Private key not found.');
        }

        if (isset($config['handler']) && is_a($config['handler'], '\GuzzleHttp\HandlerStack')) {
            $stack = $config['handler'];
        } else {
            $stack = HandlerStack::create();
        }

        if ($config['application'] === 'private') {
            $middleware = $this->getPrivateApplicationMiddleware($config);
        } else {
            $middleware = $this->getPublicApplicationMiddleware($config);
        }

        $stack->push($middleware);

        parent::__construct([
            'base_uri' => $config['base_uri'],
            'handler' => $stack,
            'auth' => 'oauth',
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
        if ($filename && realpath($filename)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $options
     *   The options passed into the constructor.
     * @return \GuzzleHttp\Subscriber\Oauth\Oauth1
     *   OAuth1 middleware.
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
     * @return \GuzzleHttp\Subscriber\Oauth\Oauth1
     *   OAuth1 middleware.
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
}
