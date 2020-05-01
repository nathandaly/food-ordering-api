<?php

declare(strict_types=1);

namespace App\FoodOrdering\Adapters;

use App\Exceptions\ProviderConnectionTimeout;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\FoodOrdering\Suppliers\FoodSoft;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

use function strlen;
use function strrchr;

/**
 * Class DatabaseFoodOrderingAdapter
 * @package App\Adapters
 */
class FoodSoftOrderingAdapter implements FoodOrderingInterface
{
    /**
     * @var FoodSoft
     */
    protected $foodSoftApi;

    /**
     * @var null|int
     */
    protected $storeParentId;

    public function __construct()
    {
        $this->foodSoftApi = resolve('FoodSoft\API');
    }

    /**
     * @param string $storeParentId
     * @return $this
     */
    public function setStoreParentId(string $storeParentId): self
    {
        $this->storeParentId = $storeParentId;

        return $this;
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getRestaurants(): Collection
    {
        return resolve('FoodSoft\API')
            ->getGroups()
            ->where('ParentID', '0')
            ->values();
    }

    /**
     * @param int $restaurantId
     * @return array
     * @throws ProviderConnectionTimeout
     */
    public function getRestaurant(int $restaurantId): array
    {
        $result = resolve('FoodSoft\API')
            ->getGroups()
            ->where('POSPRODGRPID', (string) $restaurantId)
            ->first();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getRestaurantCategories(): Collection
    {
        return $this->foodSoftApi
            ->getGroups()
            ->where('flgNode', '0')
            ->where('ParentID', '0')
            ->values();
    }

    /**
     * @return Collection
     */
    public function getRestaurantItems(): Collection
    {
        return $this->foodSoftApi
            ->getItems()
            ->where('flgSprzedaz', '1')
            ->values();
    }

    /**
     * @return Collection
     * @throws BindingResolutionException
     */
    public function getRootNodes(): Collection
    {
        $result = $this->foodSoftApi
            ->getGroups()
            ->where('ParentID', 0)
            ->values();

        if (!$result) {
            return Collection::make([]);
        }

        return $result;
    }

    /**
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->foodSoftApi
            ->getGroups()
            ->where('flgNode', '0')
            ->values();
    }

    /**
     * @param int $restaurantId
     * @return Collection
     */
    public function getCategoriesByRestaurant(int $restaurantId): Collection
    {
        return $this->getCategories()
            ->where('ParentID', (string) $restaurantId)
            ->values();
    }

    /**
     * @param int $categoryId
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getCategoryProducts(int $categoryId): Collection
    {
        $groupItemIds = $this->foodSoftApi
            ->getGroups()
            ->where('flgNode', '1')
            ->where('ParentID', (string) $categoryId)
            ->pluck('POSPRODID')
            ->values();

        $items = $this->foodSoftApi
            ->getItems()
            ->whereIn('POSPRODID', $groupItemIds)
            ->where('flgSaleAllowed', (string) '1')
            ->values();

        if (count($items) === 0) {
            return Collection::make([]);
        }

        $products = [];
        foreach ($items as $i => $item) {
            $item['Price'] = $this->getProductPrice((int) $item['POSPRODID']);
            $item['CurrencyCode'] = 'PLN';
            $products[] = $item;
            unset($items[$i]);
        }

        foreach ($products as $index => $product) {

            $products[$index]['Dimensions'] = Collection::make([]);
            $products[$index]['Addons'] = Collection::make([]);

            $dimensionType = $this->getComplexTypesById((int) $product['POSPIZZAWYMID']);
            $dimensionType['Dimension1'] = $dimensionType['Dimension2'] = Collection::make([]);

            if (!empty($dimensionType['Dimmension1'])) {
                $dimensionType['Dimension1'] = Collection::make([
                    'name' => $dimensionType['Dimmension1'],
                    'values' => $this
                        ->getDimensionsByComplexId((int) $product['POSPIZZAWYMID'])
                        ->first(),
                ]);
                unset($dimensionType['Dimmension1']);
            }

            if (!empty($dimensionType['Dimmension2'])) {
                $dimensionType['Dimension2'] = Collection::make([
                    'name' => $dimensionType['Dimmension2'],
                    'values' => $this
                        ->getDimensionsByComplexId((int) $product['POSPIZZAWYMID'])
                        ->last(),
                ]);
                unset($dimensionType['Dimmension2']);
            }

            $products[$index]['Dimensions'] = Collection::make($dimensionType);

            // Addons
            if (!empty($product['flgHasAddOns']) && (bool) $product['flgHasAddOns']) {
                $products[$index]['Addons'] = $this->foodSoftApi->getAddonByProductId((int) $product['POSPRODID']);
            }
        }

        return Collection::make($products);
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    private function getPrices(): Collection
    {
        return $this->foodSoftApi
            ->getPriceList()
            ->values();
    }

    /**
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    public function getAddons(): Collection
    {
        return $this->foodSoftApi
            ->getAddons()
            ->values();
    }

    /**
     * @param int $orderIdentifier
     * @return int
     * @throws ProviderConnectionTimeout
     */
    public function getOrderStatus(int $orderIdentifier): int
    {
        return $this->foodSoftApi->getOrderStatus($orderIdentifier);
    }

    /**
     * @param int $complexId
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    private function getComplexTypesById(int $complexId): Collection
    {
        return Collection::make(
            $this->foodSoftApi
                ->getComplexTypes()
                ->where('POSPIZZAWYMID', (string) $complexId)
                ->first() ?? []
        );
    }

    /**
     * @param int $complexId
     * @return Collection
     * @throws ProviderConnectionTimeout
     */
    private function getDimensionsByComplexId(int $complexId): Collection
    {
        $dimensionResult = Collection::make([]);
        $complexType = $this->getComplexTypesById($complexId);

        if ($complexType === null) {
            return $dimensionResult;
        }

        $dimensions = $this->foodSoftApi->getDimensions();

        $dimensions->map(
            static function(Collection $dimension) use ($complexType, $dimensionResult) {
                $dimensionToPush = $dimension
                    ->where('POSPIZZAWYMID', $complexType['POSPIZZAWYMID'])
                    ->values();

                if ($dimensionToPush->isNotEmpty()) {
                    $dimensionResult->push($dimensionToPush);
                }
            }
        );

        return Collection::make($dimensionResult->values());
    }

    /**
     * @param int $productId
     * @return int
     * @throws ProviderConnectionTimeout
     */
    private function getProductPrice(int $productId): int
    {
        $priceStructure = $this
            ->getPrices()
            ->where('POSPRODID', (string) $productId)
            ->where('AddOnID', '0')
            ->first();

        $decimalCount = strlen(substr(strrchr($priceStructure['Price'], '.'), 1));
        return (int) number_format((int) $priceStructure['Price'] , $decimalCount);
    }
}
