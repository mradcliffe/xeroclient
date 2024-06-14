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
     * @dataProvider providerGetTest
     *
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException|\GuzzleHttp\Exception\GuzzleException
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
     * @throws \Radcliffe\Xero\Exception\InvalidOptionsException
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
        return [
            [
                200,
                ['Content-Type' => 'text/xml'],
                '<?xml encoding="UTF-8" version="1.0"?><BrandingThemes><BrandingTheme><BrandingThemeID>' .
                self::createGuid() .
                '</BrandingThemeID><Name>Standard</Name><SortOrder>0</SortOrder><CreatedDateUTC>' .
                '2010-06-29T18:16:36.27</CreatedDateUTC></BrandingTheme></BrandingThemes>',
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
