<?php

namespace Radcliffe\Tests\Xero;

use PHPUnit\Framework\TestCase;

/**
 * Base test class for xero client tests.
 */
class XeroClientTestBase extends TestCase
{

    /**
     * Generate configuration options.
     *
     * @param string $api
     *   The API to use: accounting or payroll.
     *
     * @return array<string,string>
     *   An associative array of configuration options with the following keys:
     *   - base_uri: An API URL.
     *   - consumer_key: A 32-character long sring.
     *   - consumer_secret: A 32-character long string.
     */
    protected function createConfiguration(string $api = 'accounting'): array
    {
        $base_uri = 'https://api.xero.com/payroll.xro/1.0/';
        if ($api === 'accounting') {
            $base_uri = 'https://api.xero.com/api.xro/2.0/';
        }

        return [
            'base_uri' => $base_uri,
            'consumer_key' => $this->createRandomString(),
            'consumer_secret' => $this->createRandomString(),
        ];
    }

    /**
     * Generate a GUID.
     *
     * @return string
     *   A globally-unique identifier.
     */
    public static function createGuid(): string
    {
        $hash = strtoupper(hash('ripemd128', md5(openssl_random_pseudo_bytes(100))));
        $guid = substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4);
        $guid .= '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);

        return strtolower($guid);
    }

    /**
     * Get random string.
     *
     * @param int $length
     *   The length of the word.
     * @return string
     *   A random string of characters.
     */
    public static function createRandomString(int $length = 30): string
    {
        if ($length > 255) {
            throw new \InvalidArgumentException('Maximum number is 100.');
        }

        return substr(base64_encode(sha1((string) mt_rand())), 0, $length);
    }
}
