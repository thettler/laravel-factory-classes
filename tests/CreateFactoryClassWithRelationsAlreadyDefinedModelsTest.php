<?php

namespace Thettler\LaravelFactoryClasses\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\FactoryClass;
use Thettler\LaravelFactoryClasses\Relations\BelongsToManyFactoryRelation;
use Thettler\LaravelFactoryClasses\Tests\support\Models\BelongsToModel;
use Thettler\LaravelFactoryClasses\Tests\support\Models\HasOneModel;
use Thettler\LaravelFactoryClasses\Tests\support\Models\MorphToModel;

class CreateFactoryClassWithRelationsAlreadyDefinedModelsTest extends TestCase
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

        $belongsTo = new BelongsToModel();

        $hasOneFactory = $hasOneFactory->hasOne('hasOneRelation', $belongsTo);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $model = $hasOneFactory->create();

        $this->assertCount(1, HasOneModel::all());
        $this->assertCount(1, BelongsToModel::all());

        $this->assertInstanceOf(HasOneModel::class, $model);
        $this->assertInstanceOf(BelongsToModel::class, $model->hasOneRelation);

        $this->assertEquals($belongsTo->fresh(), $model->hasOneRelation);
    }

    /** @test */
    public function canCreateAModelWithAGivenHasManyRelation()
    {
        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        $belongsTo = new BelongsToModel();
        $secondBelongsTo = new BelongsToModel();

        $hasOneFactory = $hasOneFactory->hasMany('hasManyRelation', [$belongsTo, $secondBelongsTo]);

        $this->assertCount(0, HasOneModel::all());
        $this->assertCount(0, BelongsToModel::all());

        $model = $hasOneFactory->create();

        $this->assertCount(1, HasOneModel::all());
        $this->assertCount(2, BelongsToModel::all());

        $this->assertInstanceOf(HasOneModel::class, $model);

        $this->assertEquals($belongsTo->fresh(), $model->hasManyRelation[0]);
        $this->assertEquals($secondBelongsTo->fresh(), $model->hasManyRelation[1]);
    }

    /** @test */
    public function canCreateAModelWithAGivenBelongsToRelation()
    {
        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        $hasOne = new HasOneModel();
        $hasOne->save();

        $belongsToFactory = $belongsToFactory->belongsTo('belongsToOneRelation', $hasOne);

        $this->assertCount(0, BelongsToModel::all());

        $model = $belongsToFactory->create();
        $this->assertCount(1, HasOneModel::all());
        $this->assertCount(1, BelongsToModel::all());

        $this->assertInstanceOf(BelongsToModel::class, $model);
        $this->assertEquals($hasOne->fresh(), $model->belongsToOneRelation);
    }

    /** @test */
    public function canCreateAModelWithAGivenBelongsToManyRelation()
    {
        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        $hasOne = new HasOneModel();
        $hasOneSecond = new HasOneModel();

        $hasOne->save();
        $hasOneSecond->save();

        $belongsToFactory = $belongsToFactory->belongsToMany(
            'belongsToManyRelation',
            [$hasOne, $hasOneSecond]
        );

        $model = $belongsToFactory->create();

        $this->assertCount(1, BelongsToModel::all());

        $this->assertInstanceOf(BelongsToModel::class, $model);
        $this->assertEquals($hasOne->id, $model->belongsToManyRelation[0]->id);
        $this->assertEquals($hasOneSecond->id, $model->belongsToManyRelation[1]->id);
    }

    /** @test */
    public function canCreateAModelWithBelongsToManyAndPivotData()
    {
        /** @var FactoryClass $belongsToFactory */
        $belongsToFactory = $this->getBelongsToFactory()::new();

        /** @var FactoryClass $hasOneFactory */
        $hasOne = new HasOneModel();
        $hasOne->save();

        $belongsToFactory = $belongsToFactory->belongsToMany(
            'belongsToManyRelation',
            [$hasOne],
            fn (BelongsToManyFactoryRelation $relation) => $relation->pivot(['pivot' => 'data'])
        );

        $model = $belongsToFactory->create();

        $this->assertEquals('data', $model->belongsToManyRelation[0]->pivot->pivot);
    }

    /** @test */
    public function canCreateAModelWithMorphTo()
    {
        /** @var FactoryClass $morphsToFactory */
        $morphsToFactory = $this->getMorphToFactory()::new();

        $hasOne = HasOneModel::create();

        $morphsToFactory = $morphsToFactory->morphTo('morphToRelation', $hasOne);

        $this->assertCount(0, MorphToModel::all());

        $model = $morphsToFactory->create();

        $this->assertCount(1, HasOneModel::all());
        $this->assertCount(1, MorphToModel::all());

        $this->assertInstanceOf(MorphToModel::class, $model);
        $this->assertInstanceOf(HasOneModel::class, $model->morphToRelation);
    }

    /** @test */
    public function canCreateAModelWithMorphOne()
    {
        $morphsTo = MorphToModel::create([
            'morph_to_relation_id' => 'asd',
            'morph_to_relation_type' => 'asda',
        ]);

        /** @var FactoryClass $hasOneFactory */
        $hasOneFactory = $this->getHasOneFactory()::new();

        $hasOneFactory = $hasOneFactory->morphOne('morphOneRelation', $morphsTo);

        $this->assertCount(0, HasOneModel::all());

        $model = $hasOneFactory->create();

        $this->assertCount(1, HasOneModel::all());
        $this->assertCount(1, MorphToModel::all());

        $this->assertInstanceOf(HasOneModel::class, $model);
        $this->assertInstanceOf(MorphToModel::class, $model->morphOneRelation);
    }

    protected function getHasOneFactory(): string
    {
        return get_class(new class extends FactoryClass {
            protected $model = HasOneModel::class;

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
            protected $model = BelongsToModel::class;

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
            protected $model = MorphToModel::class;

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
