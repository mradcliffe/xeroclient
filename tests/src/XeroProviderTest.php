<?php

namespace Radcliffe\Tests\Xero;

use Radcliffe\Xero\XeroProvider;
use PHPUnit\Framework\TestCase;

class XeroProviderTest extends TestCase
{
    /**
     * Asserts that the valid scopes are returned based on the api.
     *
     * @param array $expected
     *   The expected result.
     * @param string $api
     *   The api parameter.
     *
     * @dataProvider validScopesProvider
     */
    public function testGetValidScopes(array $expected, $api = '')
    {
        $this->assertEquals($expected, XeroProvider::getValidScopes($api));
    }

    public function validScopesProvider()
    {
        return [
            [['offline_access'], null],
            [['offline_access', 'openid', 'profile', 'email'], 'openid'],
            [
              [
                  'offline_access',
                  'accounting.transactions',
                  'accounting.transactions.read',
                  'accounting.settings',
                  'accounting.settings.read',
                  'accounting.contacts',
                  'accounting.contacts.read',
                  'accounting.attachments',
                  'accounting.attachments.read',
                  'accounting.reports.read',
                  'accounting.journal.read',
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
            [
                ['offline_access', 'files', 'files.read'],
                'files',
            ],
            [
                ['offline_access', 'projects', 'projects.read'],
                'projects',
            ],
            [
                ['offline_access', 'paymentservices', 'bankfeeds'],
                'restricted',
            ],
        ];
    }
}
