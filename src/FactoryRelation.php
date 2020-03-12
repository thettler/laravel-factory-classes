<?php

namespace Thettler\LaravelFactoryClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Thettler\LaravelFactoryClasses\Exceptions\FactoryException;

abstract class FactoryRelation
{
    const BEFORE_TYPE = 'before';
    const AFTER_TYPE = 'after';

    protected string $type = self::AFTER_TYPE;
    protected string $relation = '';
    protected array $meta = [];
    protected Collection $relatives;

    /**
     * @param  Model  $model
     * @return Model
     */
    abstract public function make(Model $model): Model;

    /**
     * @param  Model  $model
     * @return Model
     */
    abstract public function create(Model $model): Model;

    /**
     * FactoryRelation constructor.
     *
     * @param  string  $relation
     * @param  mixed  ...$factories
     */
    public function __construct(string $relation, ...$factories)
    {
        $this->relation = $relation;
        $this->relatives = collect($factories);
    }

    /**
     * @param  string  $relation
     * @param  mixed  ...$factories
     * @return static
     */
    public static function new(string $relation, ...$factories): self
    {
        return new static($relation, ...$factories);
    }

    /**
     * @param  string  $relation
     * @return $this
     */
    public function relation(string $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @param  string  $type
     * @return $this
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param  FactoryClass  ...$factories
     * @return $this
     */
    public function factories(FactoryClass ...$factories): self
    {
        $this->relatives = $this->relatives->merge($factories);

        return $this;
    }

    /**
     * @param  Model  ...$models
     * @return $this
     */
    public function models(Model ...$models): self
    {
        $this->relatives = $this->relatives->merge($models);

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @return \Illuminate\Support\Collection|Collection
     */
    public function getRelatives()
    {
        return $this->relatives;
    }

    /**
     * @param $relative
     * @param  string  $action
     * @return Model
     * @throws FactoryException
     */
    public function convertRelative($relative, $action = 'create'): Model
    {
        if ($relative instanceof Model) {
            return $relative;
        }

        if ($relative instanceof FactoryClass) {
            return $relative->$action();
        }

        FactoryException::message('Relative must be of type '.Model::class.' or '.FactoryClass::class.'. '.get_class($relative).'given!');
    }
}
