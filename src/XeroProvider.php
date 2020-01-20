<?php

namespace Radcliffe\Xero;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Radcliffe\Xero\Exception\ResourceOwnerException;

/**
 * Implements an OAuth2 provider.
 */
class XeroProvider extends AbstractProvider
{
    protected $errorMap = [
        'invalid_client' => 'Invalid client credentials',
        'unsupported_grant_type' => 'Missing required grant_type parameter',
        'invalid_grant' => 'Invalid, expired, or already used code',
        'unauthorized_client' => 'Invalid callback URI',
    ];

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://login.xero.com/identity/connect/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://identity.xero.com/connect/token';
    }

  /**
   * {@inheritdoc}
   *
   * @throws \Radcliffe\Xero\Exception\ResourceOwnerException
   */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        throw new ResourceOwnerException();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes()
    {
        return ['offline_access'];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
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
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        throw new ResourceOwnerException();
    }

    /**
     * Gets a formatted error message from an error response.
     *
     * @param array $data
     *   The structured response message.
     *
     * @return string
     */
    protected function getResponseMessage($data)
    {
        $error = 'An unknown error occurred with this request';
        if ($data !== null && isset($data['error'])) {
            if (isset($this->errorMap[$data['error']])) {
                $error = $data['error'];
            } else {
                $error = 'Unknown error code ' . $data['error'];
            }
        }

        return $error;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Gets the valid scopes for an API.
     *
     * @param string $api
     *   (optional) One of openid, accounting, payroll_COUNTRYID, files, assets, projects, or restricted.
     *
     * @return string[]
     */
    public static function getValidScopes($api = '')
    {
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
        } elseif (substr($api, 0, 7) === 'payroll') {
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
}
