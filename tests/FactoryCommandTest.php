<?php

namespace Thettler\LaravelFactoryClasses\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use Thettler\LaravelFactoryClasses\FactoryClassServiceProvider;
use Thettler\LaravelFactoryClasses\Tests\support\Models\BelongsToModel;

class FactoryCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('factory-classes.models_path', __DIR__.'/support/Models');
        Config::set('factory-classes.factories_path', __DIR__.'/tmp');
        Config::set('factory-classes.factories_namespace', 'Thettler\LaravelFactoryClasses\Tests\Factories');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (is_dir(__DIR__.'/tmp')) {
            $files = glob(__DIR__.'/tmp/*');
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file
                }
            }
            rmdir(__DIR__.'/tmp');
        }
    }

    /**
     * @test
     */
    public function itFailsIfNoModelsFound()
    {
        $this->expectException(\LogicException::class);

        // Set to a path with no models given
        Config::set('factory-classes.models_path', __DIR__.'/');

        $this->artisan('make:factory-class');
    }

    /**
     * @test
     */
    public function itCreatesFactoryForChosenModel()
    {
        $this->artisan('make:factory-class')
            ->expectsQuestion(
                'Please pick a model',
                '<href=file://'.__DIR__.'/support/Models/SimpleModel.php>Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel</>'
            )
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/SimpleModelFactory.php'));
    }

    /** @test * */
    public function itReplacesTheTheDummyCodeInTheNewFactoryClass()
    {
        $this->artisan('make:factory-class')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/support/Models/SimpleModel.php>Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel</>')
            ->assertExitCode(0);

        $generatedFactoryContent = file_get_contents(__DIR__.'/tmp/SimpleModelFactory.php');

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'SimpleModelFactory',
                'Thettler\LaravelFactoryClasses\Tests\support\Models\SimpleModel',
                'SimpleModel',
                'create(array $extra = []): SimpleModel',
                'make(array $extra = []): SimpleModel',
            ])
        );
    }

    /** @test */
    public function itAcceptsAModelNameAsAnArgument()
    {
        if (file_exists(__DIR__.'/tmp/BelongsToModelFactory.php')) {
            unlink(__DIR__.'/tmp/BelongsToModelFactory.php');
        }

        $this->assertFalse(File::exists(__DIR__.'/tmp/BelongsToModelFactory.php'));

        $this->artisan('make:factory-class BelongsToModel')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/BelongsToModelFactory.php'));
    }

    /** @test */
    public function itFailsIfFactoryAlreadyExistsWithoutForce()
    {
        if (! file_exists(__DIR__.'/tmp/BelongsToModelFactory.php')) {
            mkdir(__DIR__.'/tmp', 0700);
            File::put(__DIR__.'/tmp/BelongsToModelFactory.php', 'w');
        }

        $this->artisan('make:factory-class BelongsToModel')
            ->expectsOutput('Factory already exists!');
    }

    /** @test */
    public function itSucceedsIfFactoryAlreadyExistsWithForce()
    {
        if (! file_exists(__DIR__.'/tmp/BelongsToModelFactory.php')) {
            mkdir(__DIR__.'/tmp', 0700);
            File::put(__DIR__.'/tmp/BelongsToModelFactory.php', 'w');
        }

        $this->artisan('make:factory-class BelongsToModel --force')
            ->expectsOutput('Thettler\LaravelFactoryClasses\Tests\Factories\BelongsToModelFactory created successfully.');
    }

    /** @test * */
    public function itLetsYouDisableAutoRelation()
    {
        $generatedFactoryContent = $this->triggerFactoryCreation(HasOneModel::class, '--without-relations');

        $this->assertFalse(
            Str::containsAll($generatedFactoryContent, [
                'public function withHasOneRelation($hasOneRelation = null): self',
                'return $this->hasOne(\'hasOneRelation\', $hasOneRelation ?? Thettler\LaravelFactoryClasses\Tests\Factories\BelongsToModelFactory::new());',
            ])
        );
    }

    /** @test */
    public function itAcceptsConfigAsOptions()
    {
        if (file_exists(__DIR__.'/tmp/BelongsToModelFactory.php')) {
            unlink(__DIR__.'/tmp/BelongsToModelFactory.php');
        }

        Config::set('factory-classes.models_path', '');
        Config::set('factory-classes.factories_path', '');
        Config::set('factory-classes.factories_namespace', '');

        $this->assertFalse(File::exists(__DIR__.'/tmp/BelongsToModelFactory.php'));

        $this->artisan('make:factory-class BelongsToModel
                --models_path='.__DIR__.'/support/Models
                --factories_path='.__DIR__.'/tmp
                --factories_namespace=Thettler\LaravelFactoryClasses\Tests\Factories
             ')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/BelongsToModelFactory.php'));
    }

    /** @test */
    public function itCanFindModelsInPassedModelsPath()
    {
        if (file_exists(__DIR__.'/tmp/BelongsToModelFactory.php')) {
            unlink(__DIR__.'/tmp/BelongsToModelFactory.php');
        }

        Config::set('factory-classes.models_path', '');
        Config::set('factory-classes.factories_path', '');
        Config::set('factory-classes.factories_namespace', '');

        $this->assertFalse(File::exists(__DIR__.'/tmp/BelongsToModelFactory.php'));

        $this->artisan('make:factory-class BelongsToModel
                --models_path='.__DIR__.'/support/Models/Models
                --factories_path='.__DIR__.'/tmp
                --factories_namespace=Thettler\LaravelFactoryClasses\Tests\Factories
             ')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/BelongsToModelFactory.php'));
    }

    /** @test * */
    public function itAddsHasOneRelationsInTheNewFactoryClass()
    {
        $generatedFactoryContent = $this->triggerFactoryCreation(HasOneModel::class);

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'public function withHasOneRelation($hasOneRelation = null): self',
                'return $this->hasOne(\'hasOneRelation\', $hasOneRelation ?? \Thettler\LaravelFactoryClasses\Tests\Factories\BelongsToModelFactory::new());',
            ])
        );
    }

    /** @test * */
    public function itAddsMorphOneRelationsInTheNewFactoryClass()
    {
        $generatedFactoryContent = $this->triggerFactoryCreation(HasOneModel::class);

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'public function withMorphOneRelation($morphOneRelation = null): self',
                'return $this->morphOne(\'morphOneRelation\', $morphOneRelation ?? \Thettler\LaravelFactoryClasses\Tests\Factories\MorphToModelFactory::new());',
            ])
        );
    }

    /** @test * */
    public function itAddsBelongsToRelationsInTheNewFactoryClass()
    {
        $generatedFactoryContent = $this->triggerFactoryCreation(BelongsToModel::class);

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'public function withBelongsToOneRelation($belongsToOneRelation = null): self',
                'return $this->belongsTo(\'belongsToOneRelation\', $belongsToOneRelation ?? \Thettler\LaravelFactoryClasses\Tests\Factories\HasOneModelFactory::new());',
            ])
        );
    }

    /** @test * */
    public function itAddsHasManyRelationsInTheNewFactoryClass()
    {
        $generatedFactoryContent = $this->triggerFactoryCreation(HasOneModel::class);

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'public function withHasManyRelation(...$hasManyRelation): self',
                'return $this->hasMany(\'hasManyRelation\', empty($hasManyRelation) ? [\Thettler\LaravelFactoryClasses\Tests\Factories\BelongsToModelFactory::new()] : $hasManyRelation);',
            ])
        );
    }

    /** @test * */
    public function itAddsBelongsToManyRelationsInTheNewFactoryClass()
    {
        $generatedFactoryContent = $this->triggerFactoryCreation(BelongsToModel::class);

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'public function withBelongsToManyRelation(...$belongsToManyRelation): self',
                'return $this->belongsToMany(\'belongsToManyRelation\', empty($belongsToManyRelation) ? [\Thettler\LaravelFactoryClasses\Tests\Factories\HasOneModelFactory::new()] : $belongsToManyRelation);',
            ])
        );
    }

    /** @test * */
    public function itAddsRelationsRecursiveInTheNewFactoryClass()
    {
        if (file_exists(__DIR__.'/tmp/HasOneModelFactory.php')) {
            unlink(__DIR__.'/tmp/HasOneModelFactory.php');
        }

        if (file_exists(__DIR__.'/tmp/BelongsToModelFactory.php')) {
            unlink(__DIR__.'/tmp/BelongsToModelFactory.php');
        }

        if (file_exists(__DIR__.'/tmp/MorphToModelFactory.php')) {
            unlink(__DIR__.'/tmp/MorphToModelFactory.php');
        }

        $this->artisan('make:factory-class --recursive')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/support/Models/BelongsToModel.php>Thettler\LaravelFactoryClasses\Tests\support\Models\BelongsToModel</>')
            ->assertExitCode(0);

        $generatedFactoryContent = file_get_contents(__DIR__.'/tmp/BelongsToModelFactory.php');

        $this->assertFileExists(__DIR__.'/tmp/HasOneModelFactory.php');
        $this->assertFileExists(__DIR__.'/tmp/BelongsToModelFactory.php');
        $this->assertFileExists(__DIR__.'/tmp/MorphToModelFactory.php');

        $this->assertTrue(
            Str::containsAll($generatedFactoryContent, [
                'public function withBelongsToManyRelation(...$belongsToManyRelation): self',
                'return $this->belongsToMany(\'belongsToManyRelation\', empty($belongsToManyRelation) ? [\Thettler\LaravelFactoryClasses\Tests\Factories\HasOneModelFactory::new()] : $belongsToManyRelation);',
                'public function withBelongsToOneRelation($belongsToOneRelation = null): self',
                'return $this->belongsTo(\'belongsToOneRelation\', $belongsToOneRelation ?? \Thettler\LaravelFactoryClasses\Tests\Factories\HasOneModelFactory::new());',
            ])
        );
    }

    protected function triggerFactoryCreation(string $model, string $options = '')
    {
        $model = class_basename($model);
        if (file_exists(__DIR__."/tmp/{$model}Factory.php")) {
            unlink(__DIR__."/tmp/{$model}Factory.php");
        }

        $this->artisan('make:factory-class '.$options)
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__."/support/Models/{$model}.php>Thettler\LaravelFactoryClasses\Tests\support\Models\\{$model}</>")
            ->assertExitCode(0);

        return file_get_contents(__DIR__."/tmp/{$model}Factory.php");
    }

    protected function getPackageProviders($app)
    {
        return [FactoryClassServiceProvider::class];
    }
}
