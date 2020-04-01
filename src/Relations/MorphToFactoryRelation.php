<?php

namespace Thettler\LaravelFactoryClasses\Relations;

use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\FactoryRelation;

class MorphToFactoryRelation extends FactoryRelation
{
    protected string $type = self::BEFORE_TYPE;

    /**
     * @param  Model  $model
     * @return Model
     * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
     */
    public function make(Model $model): Model
    {
        return $model->setRelation($this->relation, $this->convertRelative($this->relatives[0], 'make'));
    }

    /**
     * @param  Model  $model
     * @return Model
     * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
     */
    public function create(Model $model): Model
    {
        $model->{$this->relation}()->associate($this->convertRelative($this->relatives[0]));

        return $model;
    }
}
