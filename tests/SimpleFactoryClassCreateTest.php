<?php

namespace Thettler\LaravelFactoryClasses\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\FactoryClass;
use Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel;

class SimpleFactoryClassCreateTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/support/Migrations');
    }

    /** @test */
    public function canCreateASimpleModelWithFakedData()
    {
        $this->assertCount(0, SimpleModel::all());

        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $model = $factory->create();

        $this->assertCount(1, SimpleModel::all());
        $this->assertInstanceOf(SimpleModel::class, $model);
        $this->assertNotEmpty($model->id);
        $this->assertNotEmpty($model->name);
        $this->assertIsBool($model->publish);
    }

    /** @test */
    public function canCreateASimpleModelWithExtraDataAtCreateCall()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $model = $factory->create(['name' => 'lastMinuteName', 'id' => 999]);

        $this->assertEquals('lastMinuteName', $model->name);
        $this->assertEquals(999, $model->id);
    }

    /** @test */
    public function canCreateASimpleModelWithoutAnyFakeData()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $modelWithFake = $factory->create();

        $this->assertEquals('From Fake', $modelWithFake->something);

        $modelWithOutFake = $factory
            ->withoutFakeData()
            ->create(['name' => 'No Fake']);

        $this->assertNull($modelWithOutFake->something);
    }

    /** @test */
    public function addDataGivesANewFactoryInstance()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $newFactory = $factory->addData('name', 'new Name');

        $this->assertNotEquals($factory, $newFactory);
    }

    /** @test */
    public function dataGivesANewFactoryInstance()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $newFactory = $factory->data(['name' => 'new Name']);

        $this->assertNotEquals($factory, $newFactory);
    }

    /** @test */
    public function factoryGetsClonedOnEveryDataEntry()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $firstFactory = $factory->data(['name' => 'new Name']);
        $cloneFactory = $firstFactory->addData('clone', 'here');

        $this->assertEquals($cloneFactory->getData()['name'], 'new Name');
        $this->assertEquals($cloneFactory->getData()['clone'], 'here');
        $this->assertEquals($firstFactory->getData()['name'], 'new Name');
        $this->assertFalse(isset($firstFactory->getData()['clone']));
        $this->assertNotSame($firstFactory, $cloneFactory);
    }

    /** @test */
    public function factoryCanBeCloned()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $firstFactory = $factory->clone();
        $cloneFactory = $factory->clone();

        $this->assertNotSame($firstFactory, $cloneFactory);
    }

    /** @test */
    public function canCreateManyWithNoExtra()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $modelCollection = $factory->createMany(2);

        $this->assertCount(2, SimpleModel::all());
        $this->assertCount(2, $modelCollection);

        $this->assertNotEquals($modelCollection[0]->name, $modelCollection[1]->name);
    }

    /** @test */
    public function canCreateManyWithExtra()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $modelCollection = $factory->createMany(2, ['name' => 'extraName']);

        $this->assertEquals($modelCollection[0]->name, 'extraName');
        $this->assertEquals($modelCollection[1]->name, 'extraName');
    }

    /** @test */
    public function canCreateManyWithIndividualExtras()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $modelCollection = $factory->createMany(2, [['name' => 'firstName'], ['name' => 'secondName']]);

        $this->assertEquals($modelCollection[0]->name, 'firstName');
        $this->assertEquals($modelCollection[1]->name, 'secondName');
    }

    /** @test */
    public function canCreateManyWithIndividualExtrasAndUsesNoExtrasIfAreMoreCreatedThenSpecified()
    {
        /** @var FactoryClass $factory */
        $factory = $this->getValidFactory()::new();
        $modelCollection = $factory->createMany(3, [['something' => 'firstName'], ['something' => 'secondName']]);

        $this->assertEquals($modelCollection[0]->something, 'firstName');
        $this->assertEquals($modelCollection[1]->something, 'secondName');
        $this->assertEquals($modelCollection[2]->something, 'From Fake');
    }

    protected function getValidFactory(): string
    {
        return get_class(new class extends FactoryClass {
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
                return [
                    'name' => $faker->name,
                    'publish' => $this->faker->boolean,
                    'something' => 'From Fake',
                ];
            }
        });
    }
}
