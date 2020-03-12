<?php

namespace Thettler\LaravelFactoryClasses\Relations;

use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\FactoryRelation;

class BelongsToFactoryRelation extends FactoryRelation
{
    protected string $type = self::BEFORE_TYPE;

    /**
     * @param  Model  $model
     * @return Model
     * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
     */
    public function make(Model $model): Model
    {
        $relative = $this->convertRelative($this->relatives[0], 'make');

        return $model->setRelation($this->relation, $relative);
    }

    /**
     * @param  Model  $model
     * @return Model
     * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
     */
    public function create(Model $model): Model
    {
        $relative = $this->convertRelative($this->relatives[0]);
        $model->{$this->relation}()->associate($relative)->save();

        return $model;
    }
}
