<?php

namespace Radcliffe\Tests\Xero;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Radcliffe\Xero\XeroClient;

/**
 * Tests for the XeroClient class.
 *
 * @group xeroclient
 */
class XeroClientTest extends XeroClientTestBase
{

    /**
     * Asserts public application instantiation.
     */
    public function testPublicApplication(): void
    {
        $options = $this->createConfiguration();
        $client = XeroClient::createFromConfig($options + [
            'auth_token' => self::createRandomString(),
        ]);
        $this->assertNotNull($client);
    }

    /**
     * @param int $statusCode
     * @param array<string,string> $headers
     * @param string $body
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @dataProvider providerGetTest
     */
    public function testGet(int $statusCode, array $headers, string $body): void
    {
        $options = $this->createConfiguration();

        $mock = new MockHandler(
            [
                new Response($statusCode, $headers, $body)
            ]
        );
        $options['handler'] = new HandlerStack($mock);

        $client = XeroClient::createFromConfig($options, [
          'auth_token' => self::createRandomString(),
          'tenant' => '46bda23d-0659-47d6-bcaf-d1419aca0e7f',
        ]);

        $response = $client->request('GET', 'BrandingThemes');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Asserts that connections are returned and decoded.
     *
     * @param int $statusCode
     *   The HTTP status code to mock.
     * @param array<string|int,mixed> $response
     *   The response body to encode as json.
     * @param int $expectedCount
     *   The expected number of connections.
     *
     * @dataProvider connectionsResponseProvider
     */
    public function testGetConnections(int $statusCode, array $response, int $expectedCount): void
    {
        $mock = new MockHandler([
            new Response($statusCode, [
              'Content-Type' => 'application/json',
            ], json_encode($response)),
        ]);
        $client = XeroClient::createFromConfig([
          'base_uri' => 'https://api.xero.com/connections',
          'handler' => new HandlerStack($mock),
        ], ['auth_token' => self::createRandomString()]);

        $connections = $client->getConnections();
        $this->assertEquals($expectedCount, count($connections));
    }

    public function testWithInvalidUrl(): void
    {
        $this->expectException('\Radcliffe\Xero\Exception\XeroRequestException');

        $options = $this->createConfiguration();
        $options['base_uri'] = 'https://example.com/';
        $client = XeroClient::createFromConfig($options, [
          'auth_token' => self::createRandomString(),
        ]);
        $client->request('GET', 'Accounts');
    }

    public function testWithoutAuthToken(): void
    {
        $this->expectException('\Radcliffe\Xero\Exception\XeroRequestException');

        $options = $this->createConfiguration();
        $client = XeroClient::createFromConfig($options, []);
        $client->request('GET', 'Accounts');
    }

    /**
     * Provide responses for get method.
     *
     * @return array<int,mixed>
     */
    public static function providerGetTest(): array
    {
        $json = [
          'Id' => '3cc4210d-bf86-4cf5-aa5c-4a7c308dbfe1"',
          'Status' => 'OK',
          'ProviderName' => 'mradcliffe/xeroclient',
          'DateTimeUTC' => '\/Date(1718810712561)',
          'BrandingThemes' => [
            [
              'BrandingThemeID' => self::createGuid(),
              'Name' => 'Standard',
              'SortOrder' => 0,
              'CreatedDateUTC' => '2010-06-29T18:16:36.27',
            ],
          ]
        ];

        return [
            [
                200,
                ['Content-Type' => 'application/json'],
                json_encode($json),
            ]
        ];
    }

    /**
     * Test responses for the connections endpoint.
     *
     * @return array<string,mixed>
     *   An array of test cases and arguments.
     */
    public static function connectionsResponseProvider(): array
    {
        return [
            'returns tenants' => [200, [
                [
                    'id' => self::createGuid(),
                    'tenantId' => self::createGuid(),
                    'tenantType' => 'ORGANISATION',
                    'createdDateUtc' => '2020-02-02T19:17:58.1117990',
                    'updatedDateUtc' => '2020-02-02T19:17:58.1117990',
                ],
                [
                  'id' => self::createGuid(),
                  'tenantId' => self::createGuid(),
                  'tenantType' => 'ORGANISATION',
                  'createdDateUtc' => '2020-01-30T01:33:36.2717380',
                  'updatedDateUtc' => '2020-02-02T19:21:08.5739590',
                ],
            ], 2],
        ];
    }
}
