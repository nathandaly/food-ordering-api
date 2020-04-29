<?php

namespace App\Helpers;

use App\Contracts\AWSObjectInterface;
use App\Entities\Centre;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Class AWSObjectStorage
 * @package App\Helpers
 */
class AWSObjectStorage implements AWSObjectInterface
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string
     */
    private $codeOne;

    /**
     * @var string
     */
    private $codeTwo;

    /**
     * @param Centre $centre
     * @return $this
     */
    public function setCentre(Centre $centre): self
    {
        if ($centre->exists) {
            $this->configuration = [
                'isSegregated' => (bool)($centre->config['segregated'] ?? false),
                'location' => $centre->config['awsObjectLocation'] ?? 'files',
                'centreId' => $centre->id,
                'ownerId' => $centre->ownerid,
            ];
        }

        return $this;
    }

    /**
     * @return Filesystem
     */
    public function getInstance(): Filesystem
    {
        return Storage::disk('s3');
    }

    /**
     * @param $filename
     * @return bool
     */
    public function objectExists($filename): bool
    {
        return $this->getInstance()->exists($this->getObjectUrl($filename));
    }

    /**
     * @param string $object
     * @param string $access
     * @return string|null
     */
    public function saveObject(string $object, $access = 'public'): ?string
    {
        $this->checkConfiguration();

        $objectExplode = explode(';', $object);
        $base64Object = str_replace('base64,', '', $objectExplode[1] ?? $objectExplode[0]);
        $this->generateUniqueCodes();

        $putUrl = $this->getUrl($base64Object);

        if ($this->getInstance()->put(
            $this->getUrl($base64Object),
            base64_decode($base64Object),
            $access
        )) {
            return $this->getObjectUrl($putUrl);
        }

        return null;
    }


    /**
     * @param string $filename
     * @return string
     */
    public function getUrl(string $filename): string
    {
        $this->checkConfiguration();

        if ($this->isBase64($filename)) {
            $filename = $this->extractBase64FileName($filename);
        }

        return $this->buildUrl($filename);
    }

    /**
     * @param $filename
     * @return string
     */
    private function buildUrl($filename): string
    {
        $filename = trim($filename, '/');
        $uri = 'uploads' . DIRECTORY_SEPARATOR;

        if ($this->configuration['isSegregated']) {
            $uri .= $this->configuration['ownerId']
                . DIRECTORY_SEPARATOR
                . $this->configuration['centreId']
                . DIRECTORY_SEPARATOR
                . $this->configuration['location']
                . DIRECTORY_SEPARATOR
                . $filename;
        } else {
            $uri .= $this->configuration['centreId']
                . DIRECTORY_SEPARATOR
                . $this->configuration['location']
                . DIRECTORY_SEPARATOR
                . $filename;
        }

        return $uri;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getObjectUrl(string $filename): string
    {
        if ($this->isBase64($filename)) {
            $filename = $this->getUrl($filename);
        }

        return $this->getInstance()->url($filename);
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function checkConfiguration(): void
    {
        if (empty($this->configuration['centreId'])) {
            throw new RuntimeException(
                'Please make sure you call setCentre() before you use any methods in this helper.'
            );
        }
    }

    /**
     * @param string $object
     * @return string
     */
    private function extractBase64FileName(string $object): string
    {
        $object = base64_decode($object);
        $mimeType = finfo_buffer(finfo_open(), $object, FILEINFO_MIME_TYPE);
        $fileType = explode('/', $mimeType);
        $fileType = end($fileType);

        return $this->codeOne . '_' . time() . '_' . $this->codeTwo . '.' . strtolower($fileType);
    }

    /**
     * @return void
     */
    private function generateUniqueCodes(): void
    {
        $this->codeOne = Str::random(4);
        $this->codeTwo = Str::random(4);
    }

    /**
     * @param string $string
     * @return bool
     */
    private function isBase64(string $string): bool
    {
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) {
            return false;
        }

        if (($decoded = base64_decode($string, true)) === false) {
            return false;
        }

        if (base64_encode($decoded) !== $string) {
            return false;
        }

        return true;
    }
}
