<?php

namespace Thettler\LaravelFactoryClasses\Tests;


use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\FactoryClass;
use Thettler\LaravelFactoryClasses\Tests\support\Factories\SimpleFactory;
use Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel;

class FactoryClassTest extends TestCase
{
    /** @test */
    public function newOrUseMethodWorks()
    {
        $factory = SimpleFactory::new();

        $this->assertEquals('foo', $factory->newOrUse(SimpleFactory::new(), 'foo'));
        $this->assertEquals(0, $factory->newOrUse(SimpleFactory::new(), 0));
        $this->assertEquals(10, $factory->newOrUse(SimpleFactory::new(), 10));
        $this->assertEquals(false, $factory->newOrUse(SimpleFactory::new(), false));
        $this->assertEquals(true, $factory->newOrUse(SimpleFactory::new(), true));
        $this->assertEquals(['some'], $factory->newOrUse(SimpleFactory::new(), ['some']));

        $this->assertEquals(SimpleFactory::new(), $factory->newOrUse(SimpleFactory::new()));
        $this->assertEquals(SimpleFactory::new(), $factory->newOrUse(SimpleFactory::new(), null));
        $this->assertEquals(SimpleFactory::new(), $factory->newOrUse(SimpleFactory::new(), []));
    }
}
