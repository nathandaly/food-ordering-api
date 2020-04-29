<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Class IntegrationPageExists
 * @package App\Exceptions
 */
class IntegrationPageExists extends Exception
{
    /**
     * IntegrationPageExists constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
