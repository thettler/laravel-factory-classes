<?php

namespace Thettler\LaravelFactoryClasses\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\FactoryClass;
use Thettler\LaravelFactoryClasses\Relations\BelongsToManyFactoryRelation;
use Thettler\LaravelFactoryClasses\Tests\support\Models\BelongsToModel;
use Thettler\LaravelFactoryClasses\Tests\support\Models\HasOneModel;
use Thettler\LaravelFactoryClasses\Tests\support\Models\MorphToModel;

class MakeFactoryClassWithRelationsTest extends TestCase
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
    public function canCreateAModelWithAGivenHasOneRelation()
    {
        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        $hasOneFactory = $hasOneFactory->hasOne('hasOneRelation', $belongsToFactory);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $model = $hasOneFactory->make();

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $this->assertInstanceOf(HasOneModel::class, $model);
        $this->assertInstanceOf(BelongsToModel::class, $model->hasOneRelation);
    }

    /** @test */
    public function canCreateAModelWithAGivenHasManyRelation()
    {
        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();
        $secondBelongsToFactory = $this->getBelongsToFactory()::new();

        $hasOneFactory = $hasOneFactory->hasMany('hasManyRelation', [$belongsToFactory, $secondBelongsToFactory]);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $model = $hasOneFactory->make();

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $this->assertInstanceOf(HasOneModel::class, $model);
        $this->assertInstanceOf(BelongsToModel::class, $model->hasManyRelation[0]);
        $this->assertInstanceOf(BelongsToModel::class, $model->hasManyRelation[1]);
    }

    /** @test */
    public function canCreateAModelWithAGivenBelongsToRelation()
    {
        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        $belongsToFactory = $belongsToFactory->belongsTo('belongsToOneRelation', $hasOneFactory);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $model = $belongsToFactory->make();

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $this->assertInstanceOf(BelongsToModel::class, $model);
        $this->assertInstanceOf(HasOneModel::class, $model->belongsToOneRelation);
    }

    /** @test */
    public function canCreateAModelWithAGivenBelongsToManyRelation()
    {
        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();
        $hasOneSecondFactory = $this->getHasOneFactory()::new();

        $belongsToFactory = $belongsToFactory->belongsToMany(
            'belongsToManyRelation',
            [$hasOneFactory, $hasOneSecondFactory]
        );

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $model = $belongsToFactory->make();

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $this->assertInstanceOf(BelongsToModel::class, $model);
        $this->assertInstanceOf(HasOneModel::class, $model->belongsToManyRelation[0]);
        $this->assertInstanceOf(HasOneModel::class, $model->belongsToManyRelation[1]);
    }

    /** @test */
    public function canCreateAModelWithBelongsToManyAndPivotData()
    {
        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        $belongsToFactory = $belongsToFactory->belongsToMany(
            'belongsToManyRelation',
            [$hasOneFactory],
            fn (BelongsToManyFactoryRelation $relation) => $relation->pivot(['pivot' => 'data'])
        );

        $model = $belongsToFactory->make();

        $this->assertEquals('data', $model->belongsToManyRelation[0]->pivot->pivot);
    }

    /** @test */
    public function canCreateAModelWithMorphTo()
    {
        /** @var FactoryClass $morphsToFactory */
        $morphsToFactory = $this->getMorphToFactory()::new();

        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        $morphsToFactory = $morphsToFactory->morphTo('morphToRelation', $hasOneFactory);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, MorphToModel::all());

        $model = $morphsToFactory->make();

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, MorphToModel::all());

        $this->assertInstanceOf(MorphToModel::class, $model);
        $this->assertInstanceOf(HasOneModel::class, $model->morphToRelation);
    }

    /** @test */
    public function canCreateAModelWithMorphOne()
    {
        /** @var FactoryClass $morphsToFactory */
        $morphsToFactory = $this->getMorphToFactory()::new();

        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        $hasOneFactory = $hasOneFactory->morphOne('morphOneRelation', $morphsToFactory);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, MorphToModel::all());

        $model = $hasOneFactory->make();

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, MorphToModel::all());

        $this->assertInstanceOf(HasOneModel::class, $model);
        $this->assertInstanceOf(MorphToModel::class, $model->morphOneRelation);
    }

    protected function getHasOneFactory(): string
    {
        return get_class(new class extends FactoryClass {
            protected string $model = HasOneModel::class;

            public function create(array $extra = []): HasOneModel
            {
                return $this->createModel($extra);
            }

            public function make(array $extra = []): HasOneModel
            {
                return $this->makeModel($extra);
            }

            protected function fakeData(Generator $faker): array
            {
                return [];
            }
        });
    }

    protected function getBelongsToFactory(): string
    {
        return get_class(new class extends FactoryClass {
            protected string $model = BelongsToModel::class;

            public function create(array $extra = []): BelongsToModel
            {
                return $this->createModel($extra);
            }

            public function make(array $extra = []): BelongsToModel
            {
                return $this->makeModel($extra);
            }

            protected function fakeData(Generator $faker): array
            {
                return [];
            }
        });
    }

    protected function getMorphToFactory(): string
    {
        return get_class(new class extends FactoryClass {
            protected string $model = MorphToModel::class;

            public function create(array $extra = []): MorphToModel
            {
                return $this->createModel($extra);
            }

            public function make(array $extra = []): MorphToModel
            {
                return $this->makeModel($extra);
            }

            protected function fakeData(Generator $faker): array
            {
                return [];
            }
        });
    }
}
