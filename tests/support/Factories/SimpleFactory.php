<?php

namespace Thettler\LaravelFactoryClasses\Tests\support\Factories;


use Faker\Generator;
use Thettler\LaravelFactoryClasses\FactoryClass;
use Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel;

class SimpleFactory extends FactoryClass
{
    protected $model = SimpleModel::class;

    public function create(array $extra = []): SimpleModel
    {
        return $this->createModel($extra);
    }

    public function make(array $extra = []): SimpleModel
    {
        return $this->makeModel($extra);
    }

    protected function fakeData(Generator $faker): array
    {
        return [];
    }
}
