<?php

namespace Thettler\LaravelFactoryClasses\Relations;

use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\FactoryRelation;

class HasOneFactoryRelation extends FactoryRelation
{
    /**
     * @param  Model  $model
     * @return Model
     * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
     */
    public function make(Model $model): Model
    {
        $model->setRelation($this->relation, $this->convertRelative($this->relatives[0], 'make'));

        return $model;
    }

    /**
     * @param  Model  $model
     * @return Model
     * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
     */
    public function create(Model $model): Model
    {
        $model->{$this->relation}()->save($this->convertRelative($this->relatives[0]));

        return $model;
    }
}
