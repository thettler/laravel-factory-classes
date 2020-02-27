<?php

namespace Thettler\LaravelFactoryClasses;

use Faker\Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\Exceptions\FactoryException;
use Thettler\LaravelFactoryClasses\Relations\HasOneFactoryRelation;
use Thettler\LaravelFactoryClasses\Relations\MorphToFactoryRelation;
use Thettler\LaravelFactoryClasses\Relations\HasManyFactoryRelation;
use Thettler\LaravelFactoryClasses\Relations\MorphOneFactoryRelation;
use Thettler\LaravelFactoryClasses\Relations\BelongsToFactoryRelation;
use Thettler\LaravelFactoryClasses\Relations\BelongsToManyFactoryRelation;

abstract class FactoryClass
{
    protected Generator $faker;
    protected array $data = [];
    protected string $model = '';
    protected Collection $relations;
    protected bool $withOutFakeData = false;

    /**
     * This Method should be used to create and save a model
     *
     * @param  array  $extra
     * @return mixed
     */
    abstract public function create(array $extra = []);

    /**
     * This Method should be used to only create a Model
     *
     * @param  array  $extra
     * @return mixed
     */
    abstract public function make(array $extra = []);

    /**
     * Generate Fake data that gets merged with into the Model
     *
     * @param  Generator  $faker
     * @return array
     */
    abstract protected function fakeData(Generator $faker): array;

    /**
     * FactoryClass constructor.
     */
    public function __construct()
    {
        $this->checkModel();
        $this->faker = \Faker\Factory::create();
        $this->relations = collect();
    }

    /**
     * Create new FactoryClass instance
     *
     * @return static
     */
    public static function new(): self
    {
        return new static();
    }

    /**
     * Create more than one Model
     *
     * @param  int  $times
     * @param  array  $extra
     * @return Collection
     */
    public function createMany(int $times, array $extra = []): Collection
    {
        return $this->resolveMany($times, $extra, fn(array $extra) => $this->create($extra));
    }

    /**
     * Make more than one Model
     *
     * @param  int  $times
     * @param  array  $extra
     * @return Collection
     */
    public function makeMany(int $times, array $extra = []): Collection
    {
        return $this->resolveMany($times, $extra, fn(array $extra) => $this->make($extra));
    }

    /**
     * Set the data that gets used to create the Model
     *
     * @param  array  $data
     * @return $this
     */
    public function data(array $data): self
    {
        $clone = $this->clone();
        $clone->data = $data;
        return $clone;
    }

    /**
     * Merge data into the existing data array
     *
     * @param  string  $key
     * @param $value
     * @return $this
     */
    public function addData(string $key, $value): self
    {
        $clone = $this->clone();
        $clone->data[$key] = $value;
        return $clone;
    }

    /**
     * Disable the fake data generation.
     *
     * @param  bool  $withoutFakeData
     * @return $this
     */
    public function withoutFakeData(bool $withoutFakeData = true): self
    {
        $this->withOutFakeData = $withoutFakeData;
        return $this;
    }

    /**
     * HasOne Relation
     *
     * @param  string  $relation
     * @param  FactoryClass|Model  $relative
     * @param  callable|null  $configure
     * @return $this
     */
    public function hasOne(string $relation, $relative, ?callable $configure = null): self
    {
        return $this->with(HasOneFactoryRelation::new($relation, $relative), $configure);
    }

    /**
     * HasMany Relation
     *
     * @param  string  $relation
     * @param  FactoryClass[]|Model[]  $relatives
     * @param  callable|null  $configure
     * @return $this
     */
    public function hasMany(string $relation, array $relatives, ?callable $configure = null): self
    {
        return $this->with(HasManyFactoryRelation::new($relation, ...$relatives), $configure);
    }

    /**
     * BelongsTo Relation
     *
     * @param  string  $relation
     * @param  FactoryClass|Model  $relative
     * @param  callable|null  $configure
     * @return $this
     */
    public function belongsTo(string $relation, $relative, ?callable $configure = null): self
    {
        return $this->with(BelongsToFactoryRelation::new($relation, $relative), $configure);
    }

    /**
     * BelongsToMany Relation
     *
     * @param  string  $relation
     * @param  FactoryClass[]|Model[]  $relatives
     * @param  callable|null  $configure
     * @return $this
     */
    public function belongsToMany(string $relation, array $relatives, ?callable $configure = null): self
    {
        return $this->with(BelongsToManyFactoryRelation::new($relation, ...$relatives), $configure);
    }

    /**
     * MorphTo Relation
     *
     * @param  string  $relation
     * @param  FactoryClass|Model  $relative
     * @param  callable|null  $configure
     * @return $this
     */
    public function morphTo(string $relation, $relative, ?callable $configure = null): self
    {
        return $this->with(MorphToFactoryRelation::new($relation, $relative), $configure);
    }

    /**
     * MorphOne Relation
     *
     * @param  string  $relation
     * @param  FactoryClass|Model  $relative
     * @param  callable|null  $configure
     * @return $this
     */
    public function morphOne(string $relation, $relative, ?callable $configure = null): self
    {
        return $this->with(MorphOneFactoryRelation::new($relation, $relative), $configure);
    }

    /**
     * Add a custom Relation
     *
     * @param  FactoryRelation  $relation
     * @param  callable|null  $configure
     * @return $this
     */
    public function with(FactoryRelation $relation, ?callable $configure = null): self
    {
        $clone = $this->clone();
        $configure = $configure ?? fn(FactoryRelation $r) => $r;
        $clone->relations->push($configure($relation));
        return $clone;
    }

    /**
     * Clones it self
     *
     * @return $this
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData(): array
    {
        return array_merge($this->withOutFakeData ? [] : $this->fakeData($this->faker), $this->data);
    }

    /**
     * Get Model reference
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Resolve the Factory to create one Model
     *
     * @param  array  $extra
     * @return string
     */
    protected function resolveFactory(array $extra)
    {
        $model = $this->getModel();
        $model = new $model;

        $attributes = array_merge(
            $this->getData(),
            $extra
        );

        collect($attributes)->each(
            function ($attribute, string $key) use ($model) {
                $model->$key = $attribute;
            }
        );

        return $model;
    }

    /**
     * Resolve the Factory to create more than one Model
     *
     * @param  int  $times
     * @param  array  $extra
     * @param  \Closure  $action
     * @return Collection
     */
    protected function resolveMany(int $times, array $extra, \Closure $action): Collection
    {
        return collect()
            ->times($times)
            ->map(
                function ($_, $index) use ($extra, $action) {
                    if (empty($extra) || Arr::isAssoc($extra)) {
                        return $action($extra);
                    }

                    if (!array_key_exists($index, $extra)) {
                        return $action([]);
                    }

                    return $action($extra[$index]);
                }
            );
    }

    /**
     * Create and Save Model to the Database
     *
     * @param  array  $extra
     * @return mixed
     */
    protected function createModel(array $extra)
    {
        $model = $this->resolveFactory($extra);

        $this->getUpFrontRelations()
            ->each
            ->create($model);

        $model->save();
        $model = $model->fresh();

        $this->getAfterRelations()
            ->each
            ->create($model);

        return $model;
    }

    /**
     * Create and dont save Model to the Database
     *
     * @param  array  $extra
     * @return mixed
     */
    protected function makeModel(array $extra)
    {
        $model = $this->resolveFactory($extra);

        $this->getUpFrontRelations()
            ->each
            ->make($model);

        $this->getAfterRelations()
            ->each
            ->make($model);

        return $model;
    }

    /**
     * Verify that a Model is given and valid
     *
     * @return bool
     * @throws FactoryException
     */
    protected function checkModel(): bool
    {
        if ($this->model === '') {
            FactoryException::message(get_class($this).'::$model must be defined!');
        }

        if (!class_exists($this->model)) {
            FactoryException::message($this->model.'does not exist!');
        }

        return true;
    }


    /**
     * Get all Relations that need to be run before the Model is saved
     *
     * @return Collection
     */
    protected function getUpFrontRelations(): Collection
    {
        return $this->relations
            ->filter(fn(FactoryRelation $relation) => $relation->getType() === FactoryRelation::BEFORE_TYPE);
    }

    /**
     * Get all Relations that need to be run after Model is saved
     *
     * @return Collection
     */
    protected function getAfterRelations(): Collection
    {
        return $this->relations
            ->filter(fn(FactoryRelation $relation) => $relation->getType() === FactoryRelation::AFTER_TYPE);
    }

    public function __clone()
    {
        $this->relations = clone $this->relations;
    }
}
