<?php

namespace App\FoodOrdering\Suppliers;

use App\Contracts\AWSObjectInterface;
use App\Entities\Centre;
use App\Exceptions\ProviderConnectionTimeout;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Class FoodSoftSupplier
 * @package App\Suppliers
 */
class FoodSoft
{
    protected $awsObject;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * In-memory cache.
     *
     * @var Collection
     */
    protected $dimensions;

    /**
     * In-memory cache.
     *
     * @var Collection
     */
    protected $groups;

    /**
     * In-memory cache.
     *
     * @var Collection
     */
    protected $items;

    /**
     * In-memory cache.
     *
     * @var Collection
     */
    private $addons;

    /**
     * FoodSoft constructor.
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->awsObject = resolve(AWSObjectInterface::class);
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getGroups(): Collection
    {
        if ($this->groups) {
            return $this->groups;
        }

        $response = $this->makeRequest('xmlGetItemsGroupsEng');
        $groupsCollection = $this->responseToCollection($response);
        $awsObject = $this->awsObject->setCentre(request('centre'));
        $allGroups = $groupsCollection->toArray();

        // Process images
        $groupsCollection->where('Icon')->map(static function($groupWithImage, $key) use (&$allGroups, $awsObject) {
            /** @var AWSObjectInterface $awsObject */
            if (
                !$awsObject->objectExists($groupWithImage['Icon'])
                && $url = $awsObject->saveObject($groupWithImage['Icon'])
            ) {
                $groupWithImage['Icon'] = $url;
            }

            $allGroups[$key] = $groupWithImage;
        })->toArray();

        $this->groups = Collection::make($allGroups);

        return $this->groups;
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getItems(): Collection
    {
        if ($this->items) {
            return $this->items;
        }

        $response = $this->makeRequest('xmlGetItemsEng');

        $itemsCollection = $this->responseToCollection($response, 'Items');
        $awsObject = $this->awsObject->setCentre(request('centre'));
        $allItems = $itemsCollection->toArray();

        $itemsCollection->whereNotNull('Ikonka')
            ->map(static function($itemWithImage, $key) use (&$allItems, $awsObject) {
                /** @var AWSObjectInterface $awsObject */
                if (
                    !$awsObject->objectExists($itemWithImage['Ikonka'])
                    && $url = $awsObject->saveObject($itemWithImage['Ikonka'])
                ) {
                    $itemWithImage['Ikonka'] = $url;
                }

                $allItems[$key] = $itemWithImage;
            });

        $itemsCollection->whereNotNull('Zdjecie')
            ->map(static function($itemWithImage, $key) use (&$allItems, $awsObject) {
                /** @var AWSObjectInterface $awsObject */
                if (
                    !$awsObject->objectExists($itemWithImage['Zdjecie'])
                    && $url = $awsObject->saveObject($itemWithImage['Zdjecie'])
                ) {
                    $itemWithImage['Zdjecie'] = $url;
                }

                $allItems[$key] = $itemWithImage;
            });

        $this->items = Collection::make($allItems);

        return $this->items;
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getPriceList(): Collection
    {
        $response = $this->makeRequest('xmlGetItemsPriceListEng');

        return $this->responseToCollection($response);
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getComplexTypes(): Collection
    {
        $response = $this->makeRequest('xmlGetItemsComplexTypesEng');

        return $this->responseToCollection($response);
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getDimensions(): Collection
    {
        if ($this->dimensions) {
            return $this->dimensions;
        }

        $dimensionsOne = $this->makeRequest('xmlGetItemsComplexDim1Eng');
        $dimensionsOne = $this->responseToCollection($dimensionsOne);

        $dimensionsTwo = $this->makeRequest('xmlGetItemsComplexDim2');
        $dimensionsTwo = $this->responseToCollection($dimensionsTwo);

        $this->dimensions = Collection::make([$dimensionsOne, $dimensionsTwo]);

        return $this->dimensions;
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getAddons(): Collection
    {
        if ($this->addons) {
            return $this->addons;
        }

        $addonsRequest = $this->makeRequest('xmlGetItemsAddonsEng');
        $this->addons = $this->responseToCollection($addonsRequest);

        return $this->addons;
    }

    /**
     * @param int $productId
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getAddonByProductId(int $productId): Collection
    {
        $supplier = $this;
        $addonsByType = [];

        $this
            ->getAddons()
            ->where('POSPRODID', (string) $productId)
            ->groupBy('POSDODGRPID')
            ->map(static function($groupItems, $groupKey) use ($supplier, &$addonsByType) {
                $groupType = $supplier
                    ->getAddonTypes()
                    ->where('POSDODGRPID', $groupKey)
                    ->first();

                foreach ($groupItems as $item) {
                    $groupType['Products'][] = $supplier->getItems()
                        ->where('POSPRODID', $item['AddOnID'])
                        ->first();
                }

                $addonsByType[] = $groupType;
            });

        return Collection::make($addonsByType);
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getAddonTypes(): Collection
    {
        $response = $this->makeRequest('xmlGetAddonsTypesEng');

        return $this->responseToCollection($response);
    }

    /**
     * @param int $orderIdentifier
     * @return int
     * @throws ProviderConnectionTimeout
     */
    public function getOrderStatus(int $orderIdentifier): int
    {
        $response = $this->makeRequest('xmlGetOrderStatusEng');

        return $this->responseToInt($response);
    }

    /**
     * @param string $endpoint
     * @return ResponseInterface
     * @throws ProviderConnectionTimeout
     */
    private function makeRequest(string $endpoint): ResponseInterface
    {
        $response = false;

        try {
            $response = $this->httpClient->request('GET', $endpoint);
        } catch (ConnectException $e) {
            $errorNumber = !empty($e->getHandlerContext()['errno']) ?: 0;

            if ($errorNumber === 28) {
                throw new ProviderConnectionTimeout(
                    'Connection timed out while requesting data from FoodSoft.'
                );
            }
        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @param string $node
     * @return Collection
     */
    private function responseToCollection(ResponseInterface $response, string $node = 'Element'): Collection
    {
        if ($response->getStatusCode() !== 200) {
            return Collection::make([]);
        }

        $xmlContent = (string) $response->getBody();


        return $this->xmlToCollection($xmlContent, $node);
    }

    /**
     * @param ResponseInterface $response
     * @return int
     */
    private function responseToInt(ResponseInterface $response): int
    {
        if ($response->getStatusCode() !== 200) {
            return 0;
        }

        return (int) $response->getBody();
    }

    /**
     * @param string $xml
     * @param string $node
     * @return Collection
     */
    private function xmlToCollection(string $xml, string $node): Collection
    {
        try {
            $decoded = json_decode(
                str_replace('{}', '""',
                    json_encode(
                        ((array) simplexml_load_string($xml))[$node]
                    )
                ),
                true
            );
        } catch (\Exception $e) {
            $decoded = []; // TODO: Log this?
        }

        return Collection::make($decoded);
    }
}
