<?php

namespace Radcliffe\Tests\Xero;

use Prophecy\Argument;
use Prophecy\Prophet;
use Radcliffe\Xero\XeroProvider;
use PHPUnit\Framework\TestCase;

class XeroProviderTest extends TestCase
{
    protected Prophet $prophet;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    /**
     * Asserts that the valid scopes are returned based on the api.
     *
     * @param string[] $expected
     *   The expected result.
     * @param string|null $api
     *   The api parameter.
     *
     * @dataProvider validScopesProvider
     */
    public function testGetValidScopes(array $expected, ?string $api = ''): void
    {
        $custom = [];
        if ($api === 'custom') {
            $custom = $expected;
        }
        $this->assertEquals($expected, XeroProvider::getValidScopes($api, $custom));
    }

    /**
     * @return array<int,mixed>
     */
    public static function validScopesProvider(): array
    {
        return [
            [['offline_access'], null],
            [['offline_access', 'openid', 'profile', 'email'], 'openid'],
            [
              [
                  'offline_access',
                  'accounting.invoices',
                  'accounting.invoices.read',
                  'accounting.payments',
                  'accounting.payments.read',
                  'accounting.banktransactions',
                  'accounting.banktransactions.read',
                  'accounting.manualjournals',
                  'accounting.manualjournals.read',
                  'accounting.settings',
                  'accounting.settings.read',
                  'accounting.contacts',
                  'accounting.contacts.read',
                  'accounting.attachments',
                  'accounting.attachments.read',
                  'accounting.budgets.read',
                  'accounting.journals.read',
                  'accounting.reports.aged.read',
                  'accounting.reports.balancesheet.read',
                  'accounting.reports.banksummary.read',
                  'accounting.reports.budgetsummary.read',
                  'accounting.reports.executivesummary.read',
                  'accounting.reports.profitandloss.read',
                  'accounting.reports.trialbalance.read',
                  'accounting.reports.taxreports.read',
                  'accounting.reports.tenninetynine.read',
              ],
              'accounting',
            ],
            [
                [
                    'offline_access',
                    'payroll.employees',
                    'payroll.employees.read',
                    'payroll.payruns',
                    'payroll.payruns.read',
                    'payroll.payslip',
                    'payroll.payslip.read',
                    'payroll.timesheets',
                    'payroll.timesheets.read',
                    'payroll.settings',
                    'payroll.settings.read',
                ],
                'payroll_uk',
            ],
            [
              [
                'offline_access',
                'payroll.employees',
                'payroll.employees.read',
                'payroll.payruns',
                'payroll.payruns.read',
                'payroll.payslip',
                'payroll.payslip.read',
                'payroll.timesheets',
                'payroll.timesheets.read',
                'payroll.settings',
                'payroll.settings.read',
              ],
              'payroll_nz',
            ],
            [['offline_access', 'files', 'files.read'], 'files'],
            [['offline_access', 'projects', 'projects.read'], 'projects'],
            [['offline_access', 'paymentservices', 'bankfeeds'], 'restricted'],
            [['offline_access', 'assets', 'assets.read'], 'assets'],
            [['accounting.invoices.read', 'accounting.reports.banksummary.read'], 'custom'],
        ];
    }

    /**
     * Asserts that response errors are mapped correctly.
     *
     * @param array<int|string,mixed> $data
     *   The method parameter.
     * @param string $expected
     *   The expect error string.
     *
     * @dataProvider provideResponseData
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testGetResponseMessage(array $data, string $expected): void
    {
        $json = json_encode($data);
        $this->expectExceptionMessage($expected);

        $streamProphet = $this->prophet->prophesize('\Psr\Http\Message\StreamInterface');
        $streamProphet->getContents()->willReturn($json);
        $streamProphet->__toString()->willReturn($json);

        $requestProphet = $this->prophet->prophesize('\Psr\Http\Message\RequestInterface');
        $responseProphet = $this->prophet->prophesize('\Psr\Http\Message\ResponseInterface');
        $responseProphet->getStatusCode()->willReturn(400);
        $responseProphet->getBody()->willReturn($streamProphet->reveal());
        $responseProphet
            ->getHeader(Argument::containingString('content-type'))
            ->willReturn(['application/json']);
        $guzzleProphet = $this->prophet->prophesize('\GuzzleHttp\ClientInterface');
        $guzzleProphet->send(Argument::any())->willReturn($responseProphet->reveal());

        $provider = new XeroProvider();
        $provider->setHttpClient($guzzleProphet->reveal());
        $provider->getParsedResponse($requestProphet->reveal());
    }

    /**
     * Provides test arguments for ::testGetResponseMessage().
     *
     * @return array<string,mixed>
     */
    public static function provideResponseData(): array
    {
        return [
          'invalid client' => [['error' => 'invalid_client'], 'Invalid client credentials'],
          'unspported grant type' => [['error' => 'unsupported_grant_type'], 'Missing required grant_type parameter'],
          'invalid grant' => [['error' => 'invalid_grant'], 'Invalid, expired, or already used code'],
          'unauthorized' => [['error' => 'unauthorized_client'], 'Invalid callback URI'],
          'unknown' => [['error' => 'unknown'], 'Unknown error code unknown'],
          'when null' => [[], 'An unknown error occurred with this request'],
        ];
    }
}
