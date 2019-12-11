<?php

namespace Radcliffe\Xero;

interface XeroClientInterface
{

    /**
     * Get a list of valid API URLs.
     *
     * @return string[]
     */
    public static function getValidUrls();

    /**
     * Check the URL.
     *
     * @param string $base_uri
     *
     * @return bool
     *   TRUE if the base uri is valid.
     */
    public function isValidUrl($base_uri);

    /**
     * Check the private key file.
     *
     * @param string $filename
     *   The file name of the private key.
     * @return bool
     *   TRUE if the private key is valid.
     *
     * @deprecated Deprecated since 0.2.0
     */
    public function isValidPrivateKey($filename);

    /**
     * Get an unauthorized request token from the API.
     *
     * @param string $consumer_key
     *   Consumer key.
     * @param string $consumer_secret
     *   Consumer secret.
     * @param array $options
     *   An array of request options including other OAuth1 required properties depending on the application type.
     *
     * @return array
     *   An associative array consisting of the following keys:
     *   - oauth_token
     *   - oauth_secret
     *
     * @deprecated Deprecated since 0.2.0
     */
    public static function getRequestToken($consumer_key, $consumer_secret, $options = []);

    /**
     * Get an access token from the API.
     *
     * @param string $consumer_key
     *   Consumer key.
     * @param string $consumer_secret
     *   Consumer secret.
     * @param string $token
     *   OAuth token.
     * @param string $token_secret
     *   Token secret from the request token.
     * @param string $verifier
     *   The CSRF token provided by the API.
     * @param array $options
     *   An array of request options to provide to Guzzle.
     *
     * @return array
     *   An associative array consisting of the following keys:
     *   - oauth_token
     *   - oauth_secret
     *
     * @deprecated Deprecated since 0.2.0
     */
    public static function getAccessToken(
        $consumer_key,
        $consumer_secret,
        $token,
        $token_secret,
        $verifier,
        $options = []
    );

    /**
     * Create client from an existing token or code.
     *
     * Regardless of the parameters, the returned object will be a Guzzle Client
     * instance with an Authorization header using the access token for the application.
     *
     * There are three ways to do this:
     *    1. Directly with an existing access token.
     *    2. Using an authorization code retrieved within 15 minutes to get an access token.
     *    3. Using a refresh token when an access token has expired after 30 minutes.
     *
     * This will create two side-effects:
     *    1. An additional request will always be made to confirm the tenant ids allowed by
     *       the access token and stored in $this->tenantIds.
     *    2. If a code or refresh token is used, then the new access token and related
     *       information will be stored in $this->refreshedToken.
     *
     * @param string $id
     *   The Oauth2 client id.
     * @param string $secret
     *   The Oauth2 client secret.
     * @param string $token
     *   An access token, refresh token, or authorization code.
     * @param string $grant
     *   An optional grant type when refreshing or getting a new access token.
     *     - refresh_token: the provided token is a refresh token.
     *     - authorization_code: the provided token is an authorization code.
     * @param string $api
     *   The Xero API to scope to which is one of the following: accounting,
     *   payroll_COUNTRYCODE, files, assets, projects, restricted, or openid.
     * @param array $options
     *   Any additional options to pass to the constructor.
     *
     * @return static
     */
    public static function createFromToken($id, $secret, $token, $grant = null, $api = 'accounting', array $options = []);
}
