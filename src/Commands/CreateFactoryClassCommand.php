<?php

namespace Thettler\LaravelFactoryClasses\Commands;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Christophrumpel\LaravelCommandFilePicker\Traits\PicksClasses;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Filesystem\Filesystem;

class CreateFactoryClassCommand extends GeneratorCommand
{
    use PicksClasses;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:factory-class
                            {model?}
                            {--factories_path=}
                            {--models_path=}
                            {--factories_namespace=}
                            {--force}
                            {--without-relations}
                            {--recursive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new factory class.';

    /** @var string */
    protected $type = 'Factory';

    private string $modelsPath;

    private string $modelFile;

    private string $factoriesPath;

    private string $factoriesNamespace;

    private array $generateRelations = [
        HasOne::class, MorphOne::class, BelongsTo::class, HasMany::class, BelongsToMany::class,
    ];

    private array $generateManyRelations = [HasMany::class, BelongsToMany::class];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->modelsPath = $this->option('models_path') ?? config('factory-classes.models_path');
        $this->factoriesPath = $this->option('factories_path') ?? config('factory-classes.factories_path');
        $this->factoriesNamespace = $this->option('factories_namespace') ?? config('factory-classes.factories_namespace');

        $this->makeFactory();
    }

    protected function makeFactory(?Model $model = null)
    {
        $fullClassName = $this->fetchModel($model);

        $className = class_basename($fullClassName);

        $this->info("Thank you! $className it is.");

        $classPath = $this->factoriesPath.'/'.$className.'Factory.php';

        if (! $this->shouldCreateFactory($classPath)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($classPath);

        $this->files->put($classPath, $this->sortImports($this->buildClass($fullClassName)));

        $this->files->put($classPath, $this->addRelations($this->files->get($classPath), $fullClassName));

        $this->info($this->factoriesNamespace.'\\'.$className.$this->type.' created successfully.');

        return '\\'.$this->factoriesNamespace.'\\'.$className.$this->type;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/make-factory-class.stub';
    }

    protected function getArguments()
    {
        return [];
    }

    protected function fetchModel(?Model $model = null)
    {
        if ($model) {
            return get_class($model);
        }

        if ($this->argument('model')) {
            $class_finder = new ClassFinder(new Filesystem());

            return $class_finder->getFullyQualifiedClassNameFromFile($this->modelsPath.'/'.$this->argument('model').'.php');
        }

        return $this->askToPickModels($this->modelsPath);
    }

    protected function shouldCreateFactory(string $classPath)
    {
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') || ! $this->option('force')) && $this->files->exists($classPath)) {
            return false;
        }

        return true;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(['DummyFullModelClass', 'DummyModelClass', 'DummyFactory'],
            [$name, class_basename($name), class_basename($name).'Factory'], $stub);
    }

    protected function addRelations($stub, $fullClassName)
    {
        $reflection = new \ReflectionClass($fullClassName);
        $relationMethods = collect($reflection->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(function (\ReflectionMethod $method) {
                if ($returnType = $method->getReturnType()) {
                    return in_array($returnType->getName(), $this->generateRelations);
                }

                return false;
            });

        if ($relationMethods->isEmpty()
            || ($this->hasOption('without-relations')
            && $this->option('without-relations'))) {
            return str_replace('<relations>', '', $stub);
        }

        $relationMethods = $relationMethods->map(function (\ReflectionMethod $method) use ($fullClassName) {
            $relationStub = $this->getRelationStub($method);
            $relationType = class_basename($method->getReturnType()->getName());

            $relatedModel = $method->invoke(new $fullClassName)->getRelated();

            $resolvedRelationStub = str_replace(
                [
                    'dummyMethodName',
                    '$dummyRelationName',
                    'DummyRelationFactory',
                    'dummyRelationMethod',
                    'dummyRelation',
                ],
                [
                    'with'.ucfirst($method->getName()),
                    '$'.$method->getName(),
                    $this->makeRelatedFactory($relatedModel),
                    lcfirst($relationType),
                    $method->getName(),
                ],
                $relationStub
            );

            return $resolvedRelationStub;
        });

        return str_replace(['<relations>'], [$relationMethods->implode(PHP_EOL)], $stub);
    }

    protected function makeRelatedFactory(Model $relatedModel)
    {
        if (! $this->hasOption('recursive') || ! $this->option('recursive')) {
            return '\\'.$this->factoriesNamespace.'\\'.class_basename($relatedModel).'Factory';
        }

        if ($this->factoryExist(class_basename($relatedModel))) {
            return '\\'.$this->factoriesNamespace.'\\'.class_basename($relatedModel).'Factory';
        }

        return $this->makeFactory($relatedModel);
    }

    protected function factoryExist(string $model)
    {
        return $this->files->exists($this->factoriesPath.'/'.$model.'Factory.php');
    }

    protected function getRelationStub(\ReflectionMethod $method): string
    {
        if (in_array($method->getReturnType()->getName(), $this->generateManyRelations)) {
            return $this->files->get(__DIR__.'/stubs/many-relation-method.stub');
        }

        return $this->files->get(__DIR__.'/stubs/relation-method.stub');
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace([
            'DummyNamespace',
        ], [
            $this->factoriesNamespace,
        ], $stub);

        return $this;
    }
}
