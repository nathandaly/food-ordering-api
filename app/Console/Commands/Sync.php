<?php

namespace App\Console\Commands;

use App\FoodOrdering\Contracts\FoodOrderingInterface;
use Illuminate\Console\Command;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fo:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Food Soft database to our own.';

    /**
     * @var FoodOrderingInterface
     */
    protected $foodSoftService;

    /**
     * Sync constructor.
     * @param FoodOrderingInterface $foodOrdering
     */
    public function __construct(FoodOrderingInterface $foodOrdering)
    {
        parent::__construct();

        $this->foodSoftService = $foodOrdering;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->syncGroups();
    }

    private function syncGroups()
    {
        $api = $this->foodSoftService;

        // Stores
        $restaurants = $this->foodSoftService->getRestaurants();

        $restaurants->map(static function($restaurant) use ($api) {
            $categories = $api->getCategoriesByRestaurant($restaurant['POSPRODGRPID']);
            $categoryProducts = [];
            // Insert categories

            $categories->map(static function ($category) use ($api, &$categoryProducts) {
               $categoryProducts[] = $api->getCategoryProducts($category['POSPRODGRPID']);
            });


        });
    }
}
