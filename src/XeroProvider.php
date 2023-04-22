<?php

namespace Radcliffe\Xero;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Radcliffe\Xero\Exception\ResourceOwnerException;

/**
 * Implements an OAuth2 provider.
 */
class XeroProvider extends AbstractProvider
{
    /**
     * Map of error codes to English.
     *
     * @var array<string,string>
     */
    protected array $errorMap = [
        'invalid_client' => 'Invalid client credentials',
        'unsupported_grant_type' => 'Missing required grant_type parameter',
        'invalid_grant' => 'Invalid, expired, or already used code',
        'unauthorized_client' => 'Invalid callback URI',
    ];

    /**
     * Defines a list of oauth2 scopes.
     *
     * @var string[]
     */
    public static array $validScopes = [
      'offline_access', 'openid', 'profile', 'email', 'accounting.transactions', 'accounting.transactions.read',
      'accounting.reports.read', 'accounting.journals.read', 'accounting.settings', 'accounting.settings.read',
      'accounting.contacts',  'accounting.contacts.read', 'accounting.attachments', 'accounting.attachments.read',
      'payroll.employees', 'payroll.employees.read', 'payroll.payruns', 'payroll.payruns.read', 'payroll.payslip',
      'payroll.payslip.read', 'payroll.timesheets', 'payroll.timesheets.read', 'payroll.settings',
      'payroll.settings.read', 'files', 'file.read', 'assets', 'assets.read', 'projects', 'projects.read',
      'paymentservices', 'bankfeeds',
    ];

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://login.xero.com/identity/connect/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://identity.xero.com/connect/token';
    }

  /**
   * {@inheritdoc}
   *
   * @throws \Radcliffe\Xero\Exception\ResourceOwnerException
   */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        throw new ResourceOwnerException();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes(): array
    {
        return ['offline_access'];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode === 429) {
            throw new IdentityProviderException('Rate limit exceeded', $statusCode, $data);
        } elseif ($statusCode >= 400) {
            throw new IdentityProviderException($this->getResponseMessage($data), $statusCode, $data);
        }
    }

  /**
   * {@inheritdoc}
   *
   * @throws \Radcliffe\Xero\Exception\ResourceOwnerException
   */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        throw new ResourceOwnerException();
    }

    /**
     * Gets a formatted error message from an error response.
     *
     * @param array<string,string>|null $data
     *   The structured response message.
     *
     * @return string
     */
    protected function getResponseMessage(?array $data): string
    {
        $error = 'An unknown error occurred with this request';
        if ($data !== null && isset($data['error'])) {
            if (isset($this->errorMap[$data['error']])) {
                $error = $this->errorMap[$data['error']];
            } else {
                $error = 'Unknown error code ' . $data['error'];
            }
        }

        return $error;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Gets the valid scopes for an API.
     *
     * @param string|null $api
     *   (optional) One of openid, accounting, payroll_COUNTRYID, files, assets, projects, custom, or restricted.
     * @param string[] $custom
     *   (optional) Use the scopes defined here when "custom" is defined above and these scopes are valid.
     *
     * @return string[]
     */
    public static function getValidScopes(?string $api = '', array $custom = []): array
    {
        if ($api === 'custom') {
            return array_filter($custom, [__CLASS__, 'isValidScope']);
        }

        $scopes = ['offline_access'];
        if ($api === 'openid') {
            $scopes = array_merge($scopes, ['openid', 'profile', 'email']);
        } elseif ($api === 'accounting') {
            $types = ['transactions', 'settings', 'contacts', 'attachments'];
            foreach ($types as $type) {
                $scopes[] = "accounting.$type";
                $scopes[] = "accounting.$type.read";
            }
            $scopes = array_merge($scopes, ['accounting.reports.read', 'accounting.journals.read']);
        } elseif (str_starts_with($api, 'payroll')) {
            // @todo Split the logic into au, uk, nz, and other sections as necessary.
            $types = ['employees', 'payruns', 'payslip', 'timesheets', 'settings'];
            foreach ($types as $type) {
                $scopes[] = "payroll.$type";
                $scopes[] = "payroll.$type.read";
            }
        } elseif ($api === 'files') {
            $scopes = array_merge($scopes, ['files', 'files.read']);
        } elseif ($api === 'assets') {
            $scopes = array_merge($scopes, ['assets', 'assets.read']);
        } elseif ($api === 'projects') {
            $scopes = array_merge($scopes, ['projects', 'projects.read']);
        } elseif ($api === 'restricted') {
            $scopes = array_merge($scopes, ['paymentservices', 'bankfeeds']);
        }

        return $scopes;
    }

    /**
     * Checks if a scope is valid.
     *
     * @param string $scope
     *   The scope to check.
     *
     * @return bool
     *   True if the scope is a valid scope for the Xero API.
     */
    public static function isValidScope(string $scope): bool
    {
        return in_array($scope, static::$validScopes);
    }
}
