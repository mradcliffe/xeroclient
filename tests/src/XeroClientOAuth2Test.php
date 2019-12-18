<?php

namespace Radcliffe\Tests\Xero;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Radcliffe\Xero\XeroClient;

/**
 * Tests XeroClient OAuth2 code.
 *
 * @group xeroclient
 */
class XeroClientOAuth2Test extends XeroClientTestBase
{
    protected $clientId;
    protected $clientSecret;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->clientId = $this->createRandomString();
        $this->clientSecret = $this->createRandomString();
    }

    /**
     * Tests that an exception is thrown for Xero API 403 status code.
     *
     * @expectedException \League\Oauth2\CLient\Provider\Exception\IdentityProviderException
     */
    public function testCreateFromTokenError()
    {
        $mock = new MockHandler([
            new Response(403, ['Content-Type' => 'application/json'], json_encode([
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'AuthenticationUnsuccessful',
                'instance' => $this->createGuid(),
            ])),
        ]);
        $options = ['handler' => new HandlerStack($mock)];
        $httpClient = new Client($options);

        XeroClient::createFromToken(
            $this->clientId,
            $this->clientSecret,
            $this->createRandomString(),
            'refresh_token',
            'accounting',
            $options,
            ['httpClient' => $httpClient]
        );
    }

    /**
     * Tests creating from a refresh token.
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException
     */
    public function testCreateFromRefreshToken()
    {
        $token = $this->createRandomString(30);
        $refresh_token = $this->createRandomString(30);
        $tenantIdResponse = json_encode([
            [
                'id' => $this->createGuid(),
                'tenantId' => $this->createGuid(),
                'tenantType' => 'ORGANISATION',
            ],
        ]);
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $tenantIdResponse),
        ]);
        $options = ['handler' => new HandlerStack($mock)];

        // Mocks the OAuth2 Client request factory and requests.
        $refreshTokenResponse = json_encode([
            'access_token' => $token,
            'refresh_token' => $refresh_token,
            'expires' => time() + 1800,
            'token_type' => 'Bearer',
        ]);
        $providerMock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $refreshTokenResponse),
        ]);
        $providerOptions = ['handler' => new HandlerStack($providerMock)];

        $httpClient = new Client($providerOptions);

        $client = XeroClient::createFromToken(
            $this->clientId,
            $this->clientSecret,
            $token,
            'refresh_token',
            'accounting',
            $options,
            ['httpClient' => $httpClient]
        );

        $this->assertInstanceOf('\Radcliffe\Xero\XeroClient', $client);
    }

    /**
     * Tests creating from an access token.
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException
     */
    public function testCreateFromAccessToken()
    {
        $token = $this->createRandomString(30);
        $tenantIdResponse = json_encode([
            [
                'id' => $this->createGuid(),
                'tenantId' => $this->createGuid(),
                'tenantType' => 'ORGANISATION',
            ],
        ]);
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $tenantIdResponse),
        ]);
        $options = ['handler' => new HandlerStack($mock)];

        $client = XeroClient::createFromToken(
            $this->clientId,
            $this->clientSecret,
            $token,
            null,
            'accounting',
            $options
        );

        $this->assertInstanceOf('\Radcliffe\Xero\XeroClient', $client);
    }
}
