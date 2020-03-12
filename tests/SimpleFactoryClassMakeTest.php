<?php

namespace Thettler\LaravelFactoryClasses\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\FactoryClass;
use Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel;

class SimpleFactoryClassMakeTest extends TestCase
{
    /** @test */
    public function canMakeASimpleModelWithFakedData()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $model = $factory->make();

        $this->assertInstanceOf(SimpleModel::class, $model);
        $this->assertNotEmpty($model->name);
        $this->assertIsBool($model->publish);
    }

    /** @test */
    public function canMakeMany()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $modelCollection = $factory->makeMany(2);

        $this->assertCount(2, $modelCollection);

        $this->assertNotEquals($modelCollection[0]->name, $modelCollection[1]->name);
    }

    protected function getValidFactory(): string
    {
        return get_class(new class extends FactoryClass {
            protected string $model = SimpleModel::class;

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
                return [
                    'name' => $faker->name,
                    'publish' => $this->faker->boolean,
                    'something' => 'From Fake',
                ];
            }
        });
    }
}
