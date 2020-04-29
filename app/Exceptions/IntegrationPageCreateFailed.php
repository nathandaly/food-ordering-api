<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Class IntegrationPageCreateFailed
 * @package App\Exceptions
 */
class IntegrationPageCreateFailed extends Exception
{
    /**
     * IntegrationPageCreateFailed constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
