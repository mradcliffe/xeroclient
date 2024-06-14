<?php

namespace Radcliffe\Xero\Exception;

/**
 * An exception to throw when the client has invalid options.
 *
 * @deprecated in 0.5.0 and removed in 0.6.0. Use a guzzle request exception
 *             instead.
 * @see \Radcliffe\Xero\Exception\XeroRequestException
 */
class InvalidOptionsException extends \Exception
{
}
