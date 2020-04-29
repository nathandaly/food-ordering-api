<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;

/**
 * Class Repository
 * @package Bosnadev\Repositories\Eloquent
 */
abstract class Repository
{

    /**
     * @var App
     */
    private $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Repository constructor.
     * @param App $app
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function __construct(App $app) {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract public function modelClass(): string;

    /**
     * @return Model|mixed
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->modelClass());

        if (!$model instanceof Model) {
            throw new RepositoryException(
                'Class '
                . $this->modelClass()
                . ' must be an instance of '
                . Model::class
            );
        }

        return $this->model = $model;
    }
}
