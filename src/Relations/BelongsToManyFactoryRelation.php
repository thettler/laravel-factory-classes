<?php

namespace Thettler\LaravelFactoryClasses\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\FactoryRelation;

class BelongsToManyFactoryRelation extends FactoryRelation
{
    /**
     * @param  Model  $model
     * @return Model
     */
    public function make(Model $model): Model
    {
        $relatedModels = $this->relatives
            ->map(function ($factory) use ($model) {
                $relatedModel = $this->convertRelative($factory, 'make');
                $relatedModel->pivot = $model->{$this->relation}()->newPivot($this->meta['pivot'] ?? []);

                return $relatedModel;
            });

        return $model->setRelation($this->relation, Collection::make($relatedModels->all()));
    }

    /**
     * @param  Model  $model
     * @return Model
     */
    public function create(Model $model): Model
    {
        $relatedModels = $this->relatives
            ->mapWithKeys(function ($factory) {
                $model = $this->convertRelative($factory);

                return [
                    $model->getKey() => $this->meta['pivot'] ?? [],
                ];
            });
        $model->{$this->relation}()->syncWithoutDetaching($relatedModels);

        return $model;
    }

    /**
     * @param  array  $pivot
     * @return $this
     */
    public function pivot(array $pivot): self
    {
        return $this->meta('pivot', $pivot);
    }
}
