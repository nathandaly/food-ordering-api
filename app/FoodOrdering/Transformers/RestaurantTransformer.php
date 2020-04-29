<?php

declare(strict_types=1);

namespace App\FoodOrdering\Transformers;

use App\Entities\CentreConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RtfHtmlPhp\Document;
use RtfHtmlPhp\Html\HtmlFormatter;

/**
 * Class RestaurantTransformer
 * @package App\FoodOrdering\Transformers
 */
class RestaurantTransformer
{
    /**
     * @var string
     */
    protected $resourceName = 'restaurant';

    protected $config;

    /**
     * @param array $data
     * @return array
     */
    public static function transformData(array $data): array
    {
        return (new self)->transform($data);
    }

    /**
     * @param $data
     * @return array
     */
    public function transform(array $data): array
    {
        $this->config = request('centre')->config ?? [];
        $currencyCode = 'PLN';
        // TODO: Add FoodSoft value that need to come back on order as meta data.

        $restaurant = [
            'id' => $data['id'],
            'name' => $data['name'],
            'address' => $this->transformAddress($data),
        ];

        foreach ($data['categories'] as $categoryIndex => $category) {
            $products = [];

            foreach ($category['products'] as $productIndex => $product) {
                $products[$productIndex] = $this->transformProduct($product);
                if (!empty($product['CurrencyCode'])) {
                    $currencyCode = $product['CurrencyCode'];
                }

                if ($product['Addons']->isNotEmpty()) {
                    $products[$productIndex]['addons'] = $this->transformAddons($product['Addons']);
                }
            }

            $restaurant['config'] = [
                'currencyCode' => $currencyCode,
                'fees' => $data['fees'] ?? [],
            ];
            $restaurant['categories'][$categoryIndex] = $this->transformGroups($category);
            $restaurant['categories'][$categoryIndex]['products'] = $products;
        }

        return $restaurant;
    }

    /**
     * @param array $restaurant
     * @return array
     */
    private function transformAddress(array $restaurant): array
    {
        return [
            'address1' => $restaurant['address1'] ?? null,
            'address2' => $restaurant['address2'] ?? null,
            'address3' => $restaurant['address3'] ?? null,
            'address4' => $restaurant['address4'] ?? null,
            'postcode' => $restaurant['postcode'] ?? null,
        ];
    }

    /**
     * @param array $product
     * @return array
     */
    private function transformPrice(array $product): array
    {
        return [
            'value' => !empty($product['Price']) ? ($product['Price'] * 100) : 0,
            'vat' => (float) ($product['Vat'] ?? 0.00),
        ];
    }

    /**
     * @param array $product
     * @return array
     */
    private function transformWeight(array $product): array
    {
        return [
            'unit' => $product['JM'] ?? 0,
            'value' => $product['amount'] ?? 0,
        ];
    }

    /**
     * @param array $product
     * @return array
     */
    private function transformMedia(array $product): array
    {
        return [
            'icon' => $product['Icon'] ?? null,
            'picture' => $product['Picture'] ?? null,
        ];
    }

    /**
     * @param array $product
     * @return array
     */
    private function transformAttributes(array $product): array
    {
        $allergens = null;

        if (!empty($product['Allergens']) && $this->isRtfDocument($product['Allergens']))  {
            $allergenDocument = new Document($product['Allergens']);
            $allergens = (new HtmlFormatter)->Format($allergenDocument);
        }

        return [
            'symbol' => $product['Symbol'] ?? '',
            'precision' => (int) ($product['Precision'] ?? 0),
            'allergins' => $allergens,
            'calories' => (float) ($product['Calories'] ?? 0.00),
            'fat' => (float) ($product['Fats'] ?? 0.00),
            'proteins' => (float) ($product['Proteins'] ?? 0.00),
            'carbohydrates' => (float) ($product['Carbohydrates'] ?? 0.00),
        ];
    }

    /**
     * @param array $groups
     * @return array
     */
    private function transformGroups(array $groups): array
    {
        return [
            'id' => (int) $groups['POSPRODGRPID'],
            'name' => $groups['Name'],
            'description' => $groups['Description'] ?? '',
        ];
    }

    /**
     * @param Collection $dimensions
     * @return array
     */
    private function transformDimensions(Collection $dimensions): array
    {
        $dimensionGroup = [];

        if ($dimensions->isNotEmpty()) {
            foreach (['Dimension1', 'Dimension2'] as $dimensionKey) {
                if ($dimensions->get($dimensionKey)->isNotEmpty()) {
                    $dimensionGroup[$dimensionKey]['name'] = $dimensions->get($dimensionKey)['name'];

                    foreach ($dimensions->get($dimensionKey)['values'] as $dimValue) {
                        $dimensionGroup[$dimensionKey]['values'][] = [
                            'id' => (int) $dimValue['Id'],
                            'name' => $dimValue['Name'],
                            'default' => (int) $dimValue['flgDefault'],
                            'complexTypeId' => (int) $dimValue['POSPIZZAWYMID'],
                        ];
                    }
                }
            }
        }

        return array_values($dimensionGroup);
    }

    /**
     * @param Collection $addonGroups
     * @return array
     */
    private function transformAddons(Collection $addonGroups): array
    {
        $transformer = $this;
        $groups = [];

        $addonGroups->map(static function($addonGroup) use (&$groups, $transformer) {
            $transformedGroup = [
                'choice' => (int) ($addonGroup['flgWybor'] ?? 0),
                'name' => $addonGroup['Nazwa'],
                'mandatory' => (int) ($addonGroup['flgObowiazkowy'] ?? 0),
                'maxQuantity' => (int) ($addonGroup['MaxIlosc'] ?? 0),
                'minQuantity' => (int) ($addonGroup['MinIlosc'] ?? 0),
            ];

            $products = [];

            foreach ($addonGroup['Products'] as $product) {
                $products[] = $transformer->transformProduct($product);
            }

            $transformedGroup['products'] = $products;
            $groups[] = $transformedGroup;
        });

        return $groups;
    }

    /**
     * @param array $product
     * @return array
     */
    private function transformProduct(array $product): array
    {
        $transformedProduct = [
            'id' => (int) $product['POSPRODID'],
            'name' => $product['Nazwa'] ?? $product['Name'] ?? null,
            'description' => $product['Opis'] ?? $product['Description'] ?? null,
        ];

        $transformedProduct['price'] = $this->transformPrice($product);
        $transformedProduct['attributes'] = $this->transformAttributes($product);
        $transformedProduct['media'] = $this->transformMedia($product);
        $transformedProduct['weight'] = $this->transformWeight($product);

        if (!empty($product['Dimensions'])) {
            $transformedProduct['dimensions'] = $this->transformDimensions($product['Dimensions']);
        } else {
            $transformedProduct['dimensions'] = [];
        }

        return $transformedProduct;
    }

    /**
     * @param string $subject
     * @return bool
     */
    private function isRtfDocument(string $subject): bool
    {
        return Str::startsWith(str_replace('{', '', $subject), '\rtf');
    }
}
