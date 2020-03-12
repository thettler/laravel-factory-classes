<?php

namespace Thettler\LaravelFactoryClasses\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\FactoryRelation;

class HasManyFactoryRelation extends FactoryRelation
{
    /**
     * @param  Model  $model
     * @return Model
     */
    public function make(Model $model): Model
    {
        $model->setRelation(
            $this->relation,
            Collection::make($this->relatives->map(fn ($relative) => $this->convertRelative($relative, 'make'))->all())
        );

        return $model;
    }

    /**
     * @param  Model  $model
     * @return Model
     */
    public function create(Model $model): Model
    {
        $model->{$this->relation}()
            ->saveMany($this->relatives->map(fn ($relative) => $this->convertRelative($relative, 'make')));

        return $model;
    }
}
