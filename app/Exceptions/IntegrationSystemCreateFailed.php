<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Class IntegrationSystemCreateFailed
 * @package App\Exceptions
 */
class IntegrationSystemCreateFailed extends Exception
{
    /**
     * @var string
     */
    protected $systemName;

    /**
     * IntegrationSystemCreateFailed constructor.
     * @param string $systemName
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($systemName = '', $message = '', $code = 0, Throwable $previous = null)
    {
        $this->systemName = $systemName;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getSystemName(): string
    {
        return $this->systemName;
    }
}
