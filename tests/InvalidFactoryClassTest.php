<?php

namespace Thettler\LaravelFactoryClasses\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\Exceptions\FactoryException;
use Thettler\LaravelFactoryClasses\FactoryClass;

class InvalidFactoryClassTest extends TestCase
{
    /** @test */
    public function mustHaveAModelAttribute()
    {
        $this->expectException(FactoryException::class);
        $this->getInValidWithoutModelFactory()::new();
    }

    /** @test */
    public function mustHaveAModelAttributeWithExistingModel()
    {
        $this->expectException(FactoryException::class);
        $this->getInValidWithNotExistingModelFactory()::new();
    }

    protected function getInValidWithoutModelFactory(): string
    {
        return get_class(new class extends FactoryClass {
            /* IS MISSING */
            //protected string $model = SimpleModel::class;

            public function create(array $extra = [])
            {
            }

            public function make(array $extra = [])
            {
            }

            protected function fakeData(Generator $faker): array
            {
                return [];
            }
        });
    }

    protected function getInValidWithNotExistingModelFactory(): string
    {
        return get_class(new class extends FactoryClass {
            protected $model = 'Some/Not/Existing/Model';

            public function create(array $extra = [])
            {
            }

            public function make(array $extra = [])
            {
            }

            protected function fakeData(Generator $faker): array
            {
                return [];
            }
        });
    }
}
