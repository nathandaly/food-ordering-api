<?php

namespace App\Contracts;

use App\Entities\Centre;
use App\Helpers\AWSObjectStorage;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Interface AWSObjectInterface
 * @package App\Contractså
 */
interface AWSObjectInterface
{
    /**
     * @param Centre $centre
     * @return AWSObjectStorage
     */
    public function setCentre(Centre $centre): AWSObjectStorage;

    /**
     * @return Filesystem
     */
    public function getInstance(): Filesystem;

    /**
     * @param string $object
     * @param string $access
     * @return string|null
     */
    public function saveObject(string $object, $access = 'public'): ?string;

    /**
     * @param string $filename
     * @return string
     */
    public function getObjectUrl(string $filename): string;

    /**
     * @param string $filename
     * @return string
     */
    public function getUrl(string $filename): string;

    /**
     * @param $filename
     * @return bool
     */
    public function objectExists($filename): bool;
}
