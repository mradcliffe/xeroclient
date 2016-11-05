<?php

namespace Radcliffe\Xero;

interface XeroClientInterface
{

    /**
     * Get a list of valid API URLs.
     *
     * @return array
     */
    public static function getValidUrls();

    /**
     * Check the URL.
     *
     * @param string $base_uri
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
     */
    public function isValidPrivateKey($filename);

    /**
     * Get an unauthorized request token from the API.
     *
     * @param string $consumer_key
     *  Consumer key.
     * @param string $consumer_secret
     *  Consumer secret.
     * @param array $options
     *  An array of request options including other OAuth1 required properties depending on the application type.
     * @return array
     *   An associative array consisting of the following keys:
     *   - oauth_token
     *   - oauth_secret
     */
    public static function getRequestToken($consumer_key, $consumer_secret, $options = []);

    /**
     * Get an access token from the API.
     *
     * @param string $consumer_key
     *  Consumer key.
     * @param string $consumer_secret
     *  Consumer secret.
     * @param string $token
     *  OAuth token.
     * @param string $token_secret
     *  Token secret from the request token.
     * @param string $verifier
     *  The CSRF token provided by the API.
     * @param array $options
     *  An array of request options to provide to Guzzle.
     * @return array
     *   An associative array consisting of the following keys:
     *   - oauth_token
     *   - oauth_secret
     */
    public static function getAccessToken(
        $consumer_key,
        $consumer_secret,
        $token,
        $token_secret,
        $verifier,
        $options = []
    );
}
