<h1 align="center">Welcome to Laravel Factory Classes 👋</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thettler/laravel-factory-classes.svg?style=flat-square)](https://packagist.org/packages/thettler/laravel-factory-classes)
[![License](https://poser.pugx.org/thettler/laravel-factory-classes/license)](https://packagist.org/packages/thettler/laravel-factory-classes) 
[![Total Downloads](https://img.shields.io/packagist/dt/thettler/laravel-factory-classes.svg?style=flat-square)](https://packagist.org/packages/thettler/laravel-factory-classes)
<a href="https://twitter.com/TobiSlice" target="_blank">
  <img alt="Twitter: TobiSlice" src="https://img.shields.io/twitter/follow/TobiSlice.svg?style=social" />
</a>

Laravel Factory Classes is a package to help you creating Models with data through Factory Classes with a fluent API and automatic auto completion for your tests, seeder or everywhere you might want them.

### :bulb: Why would you want to use this package?
Creating Factories for all your Models can get pretty messy sometimes. It lacks autocomplete, you often have to know about
the business logic of the Model to get it to a specific state.
A Factory class solves those problems. Every Model has its own Factory which contains everything necessary to create and fill it with data.
You can simply use the API of the Factory to instantiate, save or alter the Model.   
To know more about this topic I suggest [this blog post](https://stitcher.io/blog/laravel-beyond-crud-09-test-factories) from [sticher.io](sticher.io).

## Example
Think of a use case where we have a concert Model with a venue (belongsTo), supporting acts (belongsToMany), headliners (belongsToMany) and some 
other attributes we don't care about in this example. With normal Factories you would do something like this to create a concert:
```php
$concert = factory(Concert::class)->create([
    'date' => now()
]);
$venue = factory(Venue::class)->create();
$headliner = factory(Artist::class)->create();
$support = factory(Artist::class)->create();

$concert->venue()->associate($venue);
$concert->lineup()->attach($headliner->id, ['headliner'=> true]);
$concert->lineup()->attach($support->id, ['headliner'=> false]);

$this->assert($concert, /*Do something with your Concert*/);
``` 
With this package it would look like this: 
```php
$concert = ConcertFatory::new()
            ->date(now())
            ->withVenue()
            ->withHeadliner()
            ->withSupport()
            ->create();

$this->assert($concert, /*Do something with your Concert*/);
``` 
and the Factory class looks like this:
```php
<?php

namespace App\Factories;

use App\Concert;
use Faker\Generator;
use Illuminate\Support\Carbon;
use Thettler\LaravelFactoryClasses\FactoryClass;

class ConcertFactory extends FactoryClass
{
    protected string $model = Concert::class;

    protected function fakeData(Generator $faker): array
    {
        return [
            'date' => $faker->dateTimeBetween('-5 years', '+5 years'),
            /* More attributes*/
        ];
    }

    public function create(array $extra = []): Concert
    {
        return $this->createModel($extra);
    }

    public function make(array $extra = []): Concert
    {
       return $this->makeModel($extra);
    }

    public function date(Carbon $data): self
    {
        return $this->addData('date', $data);
    }

    public function withVenue($venueFactory = null): self
    {
        return $this->belongsTo('venue', $venueFactory ?? VenueFactory::new());
    }

    public function withHeadliner(...$artistFactories): self
    {
        return $this->belongsToMany(
            'lineups',
            empty($artistFactories) ? [ArtistFactory::new()] : $artistFactories,
            fn(BelongsToManyFactoryRelation $relation) => $relation->pivot(['headliner' => true])
        );
    }

    public function withSupport(...$artistFactories): self
    {
        return $this->belongsToMany(
            'lineups',
            empty($artistFactories) ? [ArtistFactory::new()] : $artistFactories,
            fn(BelongsToManyFactoryRelation $relation) => $relation->pivot(['headliner' => true])
        );
    }

}

```

## :scroll: Features
* Create Models and save them to the DB
* Make Models without saving them to the DB
* Create Models with relations
* Create all the Factories you need with one command (`--recursive`)
* Automatically generated dummy data for your Models
* Also usable outside of test without dummy data generation
* Extract Model creation logic to it's own class
* Full control over relations and attributes at any time

## :computer: Installation
You can install the package via composer:
```bash
composer require thettler/laravel-factory-classes
```
To publish the config file run:
```bash
php artisan vendor:publish --provider="Thettler\LaravelFactoryClasses\FactoryClassServiceProvider"
```

It will provide the package's config file where you can define the path of your Models, the path of the generated Factories, as well as the generated Factories namespace

```php
<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'models_path' => base_path('app'),

    'factories_path' => base_path('app/Factories'),

    'factories_namespace' => 'App\Factories',
];
```

## :rocket: Getting Started
### The Quick Way
To create a new Factory you can use the following command:
```bash
php artisan make:factory-class
```
This will prompt you a list with Models, it knows where your models live from your config. Here you choose the Model you want to create.
This will give you a new `Factory` under the `App\Factories` namespace and add all of its relations automatically if you type hint them inside your
Model with their return value. More About [Relations](#relations).
To Understand what this Command generated for you read the [Manual Guide](#The-Manual-Way)

#### Additional Arguments And Options
If you don't want to select your Model from a list, you can pass the class name of a Model in your Model path as an argument and your Factory will immediately be created for you:
```bash
php artisan make:factory-class User
```
By default, this command will stop and give you an error if a Factory you're trying to create already exists. You can overwrite an existing Factory using the force option:
```bash
php artisan make:factory-class User --force
```
A quick way to build a lot of new Factories is to use the `--recursive` flag. It will then go through all the relations of the chosen class and create Factories for them as well if there aren't any already.
```bash
php artisan make:factory-class User --recursive
```
If you don't want the relation methods automatically created, you can use this flag:
```bash
php artisan make:factory-class User --without-relations
```
You can also overwrite the config using the command:
`--models_path=app/models`

`--factories_path=path/to/your/factories`

`--factories_namespace=Your\Factories\Namespace`

### The Manual Way
To create your first `FactoryClass` you simply create a normal PHP Class and extend the abstract `Thettler\LaravelFactoryClasses\FactoryClass` Class.
This Class requires you to define 3 Methods and one attribute:

1. First you have to add a `protected string $model` attribute to the factory. It contains the reference to the Model the Factory should create.
So for example the standard `User` Class Laravel ships with:
```php
<?php
namespace App\Factories;

use Thettler\LaravelFactoryClasses\FactoryClass;
use App\User;

class UserFactory extends FactoryClass {
      protected string $model = User::class;
}
```

2. After you told the Factory which Model it should create you have to define a `public create()` function. It expects a parameter 
`$extra` which is an array with the default value of an empty array. More on the purpose of `$extra` later.
Here you should also typehint the Model Class as the return value. This will give you autocomplete in the most editors.
Finally you call and return the `$this->createModel($extra)` method. This method will take care of creating the Model for you.
You still got the freedom to alter the Model after its creation or completely create it on your own. 
So with our `User` Class it looks like this:   
```php
<?php
namespace App\Factories;

use Thettler\LaravelFactoryClasses\FactoryClass;

class UserFactory extends FactoryClass {
      /*...*/

     public function create(array $extra = []): User
      {
          return $this->createModel($extra);
      }
}
```

3. Now you have to add a `public make()` function. It's pretty much the same as with `create()`, but here you call the 
`$this->makeModel($extra)` method instead of `$this->createModel($extra)`. We will talk about the difference between `create()` and `make()`
in a moment.
So with our `User` Class it looks like this:   
```php
<?php
namespace App\Factories;

use Thettler\LaravelFactoryClasses\FactoryClass;

class UserFactory extends FactoryClass {
      /*...*/

     public function make(array $extra = []): User
      {
          return $this->makeModel($extra);
      }
}
```

4. The last method you have to add is `protected fakeData()`. This method gets a `Faker` instance as a parameter and expects you to 
return an Array. The `fakeData()` method is used to generate default data for your Models. It returns an associative array with the name of the attribute as key and the value you want to set.  
So with our `User` Class it looks like this:   
```php
<?php
namespace App\Factories;

use Thettler\LaravelFactoryClasses\FactoryClass;

class UserFactory extends FactoryClass {
      /*...*/

    protected function fakeData(\Faker\Generator $faker): array
    {
        return [
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'remember_token' => Str::random(10),
        ];
    }
}
```

Now you are good to go and have created your first FactoryClass.
The complete Class now looks like this:

```php
<?php
namespace App\Factories;

use Thettler\LaravelFactoryClasses\FactoryClass;
use App\User;

class UserFactory extends FactoryClass {

        protected string $model = User::class;

        public function create(array $extra = []): User
        {
            return $this->createModel($extra);
        }

        public function make(array $extra = []): User
        {
            return $this->makeModel($extra);
        }

        protected function fakeData(\Faker\Generator $faker): array
        {
            return [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('secret'),
                'remember_token' => Str::random(10),
            ];
        }
}
```

## Using Factories

### Creating/Making Models
Now that you have created your `FactoryClass` you'll want to use it. To instantiate your `FactoryClass` use the `static new()` method.
```php
$userFactory = UserFactory::new();
```

To create and save a Model to the Database now call the `create()` method. This will create a Model with the data you have defined
inside of the `fakeData()`
```php
$userFactory = UserFactory::new();
$userModel = $userFactory->create();
```

If you don't want to store the Model in the database use the `make()` method.
```php
$userFactory = UserFactory::new();
$userModel = $userFactory->make();
```

In case you need more than one Model you can use the `createMany()` and `makeMany()` methods. Both take an int as parameter, which indicates how many Models 
should be created, and return a `Collection` with the Models.
```php
$threeSavedUserCollection = UserFactory::new()->createMany(3);
$threeNotSavedUserCollection = UserFactory::new()->makeMany(3);
```

### Changing Data
In most cases you want to change some attributes depending on the situation and don't want to use all of the `fakeData`. This
Package gives you 3 options for changing the data that is used to create the Model.

1. Use the `addData()` method. This method takes the name of an attribute as the first parameter and the value which should be set as the second one.
 ```php
$user = UserFactory::new()
            ->addData('name', 'myName')
            ->create();

echo $user->name; // myName
 ```

2. The second option is to use the `data()` method. This method takes an array of key value pairs which represent the attributes and their values.
This method will also overwrite all of the previous set data
```php
 $user = UserFactory::new()
            ->data(['name' => 'myName'])
            ->create();
 echo $user->name; // myName
```

> :warning: `data()` and `addData()` will automatically give you a new instance of the Factory to prevent side effects

3. The Last option is using the `$extra` parameter on the `create()`, `createMany()`,`make()` or `makeMany()` methods.
This will overwrite the previous data with the same key.
```php
$user = UserFactory::new()->create(['name' => 'extraName']);
echo $user->name; // extraName

$user = UserFactory::new()->make(['name' => 'extraName']);
echo $user->name; // extraName

$userCollection = UserFactory::new()->createMany(2, ['name' => 'extraName']);
$userCollection->pluck('name')->all(); // ['extraName', 'extraName']

$user = UserFactory::new()->makeMany(2, ['name' => 'extraName']);
$userCollection->pluck('name')->all(); // ['extraName', 'extraName']
```
But the `$extra` for `createMany()` and `makeMany()` are a little bit different from `create()` and `make()`.
Here you can also pass a nested array to give the Models in the Collection different data.
```php
$userCollection = UserFactory::new()->createMany(2, [['name' => 'firstName'], ['name' => 'secondName']]);
$userCollection->pluck('name')->all(); // ['firstName', 'secondName']
```
```php
$userCollection = UserFactory::new()->createMany(3, [['name' => 'firstName'], ['name' => 'secondName']]);
$userCollection->pluck('name')->all(); // ['firstName', 'secondName', '<value-from-fakeData>']
```
#### :sparkles: Tip:
If you want a more readable API, you can add little helper setters to your Factory class, like this:
```php
use Thettler\LaravelFactoryClasses\FactoryClass;

class UserFactory extends FactoryClass {
      /*...*/

    public function name(string $name): self
    {
        return $this->addData('name', $name);
    }
}
```
```php
 $user = UserFactory::new()
            ->name('myName')
            ->create();
 echo $user->name; // myName
```

### Relations
Creating Models with relations is often a little bit tedious. This Package has got your back with a simple, yet powerful
relation system that uses your already defined relations on the Model.

All relations are working pretty much the same,  they take the relation name as first Parameter and a `FactoryClass` or `Model` as the second one.
They also have a third parameter that lets you hook into the relation and modify its behavior but we'll save that for the 
[Advanced](#advanced) part for now. 

If you provide a `FactoryClass` as second parameter the relation will create a new Model from the `FactoryClass` every time you call `create()`, `createMany()`,`make()` or `makeMany()` 
and attach it to the Model Relation.

> :warning: Same as with `data()` and `addData()` all relation methods will give you a new instance of the Factory to prevent side effects.

For all the examples we assume that the Model has a Company relation that is called `company`. 
#### HasOne
```php
class User extends Model {
    public function company(): HasOne {
        return $this->hasOne(Company::class);
    }
}
```
```php
$user = UserFactory::new()->hasOne('company', CompanyFactory::new())->create();
$user->company; // Some new created Company
// Or
$company = Company::find('xy');
$user = UserFactory::new()->hasOne('company', $company)->create();
$user->company; // Company XY
```

#### HasMany
The `hasMany()` method takes an array of `FactoryClasses` or `Models` as second parameter.
```php
class User extends Model {
    public function companies(): HasMany {
        return $this->hasMany(Company::class);
    }
}
```
```php
$user = UserFactory::new()->belongsTo('companies',[CompanyFactory::new()])->create();
$user->companies[0]; // Some new created Company
// Or
$company = Company::find('xy');
$user = UserFactory::new()->belongsTo('companies', [$company])->create();
$user->companies[0]; // Company XY
```

#### BelongsTo
```php
class User extends Model {
    public function company(): BelongsTo {
        return $this->belongsTo(Company::class);
    }
}
```
```php
$user = UserFactory::new()->belongsTo('company', CompanyFactory::new())->create();
$user->company; // Some new created Company
// Or
$company = Company::find('xy');
$user = UserFactory::new()->belongsTo('company', $company)->create();
$user->company; // Company XY
```

#### BelongsToMany
Just like `hasMany()`, `belongsToMany()` takes an array of `FactoryClasses` or `Models`:
```php
class User extends Model {
    public function companies(): BelongsToMany {
        return $this->belongsToMany(Company::class);
    }
}
```
```php
$user = UserFactory::new()->belongsToMany('companies', CompanyFactory::new())->create();
$user->companies[0]; // Some new created Company
// Or
$company = Company::find('xy');
$user = UserFactory::new()->belongsToMany('companies', $company)->create();
$user->companies[0]; // Company XY
```
If you want to add pivot data to your `belongsToMany()` relation, you can use the third parameter. Its a callback
that gives you an Instance of `BelongsToManyFactoryRelation`. On this class you can call the `pivot()` method and return it again
to add your pivot data.
You can find more about the third parameter in the [Advanced](#advanced) part.
```php
class User extends Model {
    public function companies(): BelongsToMany {
        return $this->belongsToMany(Company::class)->withPivot('role');
    }
}
```
```php
$user = UserFactory::new()
         ->belongsToMany(
            'companies', 
            CompanyFactory::new(),
            fn(BelongsToManyFactoryRelation $relation) => $relation->pivot(['role' => 'manager'])
         )
         ->create();
$user->companies[0]->role; // manager
```

#### MorphTo
```php
class User extends Model {
    public function company(): MorphTo {
        return $this->morphTo(Company::class);
    }
}
```
```php
$user = UserFactory::new()->morphTo('company', CompanyFactory::new())->create();
$user->company; // Some new created Company
// Or
$company = Company::find('xy');
$user = UserFactory::new()->morphTo('company', $company)->create();
$user->company; // Company XY
```

#### MorphOne
```php
class User extends Model {
    public function company(): MorphOne {
        return $this->morphOne(Company::class);
    }
}
```
```php
$user = UserFactory::new()->morphOne('company', CompanyFactory::new())->create();
$user->company; // Some new created Company
// Or
$company = Company::find('xy');
$user = UserFactory::new()->morphOne('company', $company)->create();
$user->company; // Company XY
```

#### :sparkles: Tip:
On your `FactoryClass` create little helpers with default values for your relations so you can call them without explicitly 
giving them a `FactoryClass` or Model:
```php
use Thettler\LaravelFactoryClasses\FactoryClass;

class UserFactory extends FactoryClass {
      /*...*/

    public function withCompany($company = null): self
    {
        return $this->belongsTo('company', $company ?? CompanyFactory::new());
    }
}
```
```php
 $user = UserFactory::new()
            ->withCompany()
            ->create();
 echo $user->company; // Company XY
```
or for multiple relations: 
```php
use Thettler\LaravelFactoryClasses\FactoryClass;

class UserFactory extends FactoryClass {
      /*...*/

    public function withCompanies(...$companies): self
    {
        return $this->belongsToMany('companies', empty($companies) ? [CompanyFactory::new()] : $companies);
    }
}
```
```php
 $user = UserFactory::new()
            ->withCompanies()
            ->create();
 echo $user->companies[0]; // Company
```

The command will generate those methods automatically for you if you define a return type on your Model for your Relations.
```php
class User extends Model {
    // Would generate a withCompany() method on your factory
    public function company(): BelongsTo {
        return $this->belongsto(Company::class);
    }
    // Would not generate a withCompany() method on your factory
    public function company() {
        return $this->belongsto(Company::class);
    }
}
```
> :warning: The automatic method creation does not work for `MorphTo` relations

### Disable Fake Data generation
If you don't want fake data to be generated for your Model you can use the `withoutFakeData()` method on the Factory.
```php
 $user = UserFactory::new()
            ->withoutFakeData()
            ->make();
```

## :hammer: Advanced

### Customize Relations
Every relation has its dedicated class that takes care of creating the relations. You can hook into the class and modify it by using
the third parameter of the relation functions.
It is a Callable that receives an instance of `FactoryRelation` and returns an instance of `FactoryRelation`.

```php
use Thettler\LaravelFactoryClasses\FactoryClass;
use App\User;

class UserFactory extends FactoryClass {

         /* ... */

        public function withCompany ($company) {
            return $this->belongsTo(
                'company', 
                $company, 
                fn(\Thettler\LaravelFactoryClasses\Relations\BelongsToFactoryRelation $relation) => $relation
                ->type('before') // after|before this indicates if the relation creation should take place before or after the main Model has ben created and saved to the DB
                ->relation('diffrentCompany') // change the name of the relation that gets used so here it changes from 'company' to 'differentCompany'
                ->factories(CompanyFactory::new()->withSpecialState()) // Lets you add an one or more Factories to the Relation !! only the many relations using mor then one factory
                ->models(Company::find('xy')) // Same as factories() only for Models 
            );
        }
}
```

### Using own Relations
If you need more complex Relations you can write your own `FactoryRelations` and use them in your Factories.
To do so use the `with()` method on your `FactoryClass` and Pass your relation as first Parameter through. 

```php
use Thettler\LaravelFactoryClasses\FactoryClass;
use App\User;

class UserFactory extends FactoryClass {

         /* ... */

        public function withCompany ($company) {
            return $this->with(MyFactoryRelation::new('company', $company));
        }
}
```

To Create your `FactoryRelation` make a new PHP Class and Extend the `FactoryRelation` class. Define the Abstract methods and you are good to
go. For more examples look under `src/Relations`.

```php
namespace Thettler\LaravelFactoryClasses\Relations;

use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelFactoryClasses\FactoryRelation;

class BelongsToFactoryRelation extends FactoryRelation
{
    protected string $type = self::BEFORE_TYPE; // after|before this indicates if the relation creation should take place before or after the main Model has ben created and saved to the DB

    /**
    * This Method will be used if the make() or makeMany() method gets used it gets the current Model (in our case User) and returns this Model again
    * 
    * @param Model $model
    * @return Model
    * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
    */
    public function make(Model $model): Model
    {
        $relative = $this->convertRelative($this->relatives[0], 'make');
        return $model->setRelation($this->relation, $relative);
    }

    /**
    * This Method will be used if the create() or createMany() method gets used it gets the current Model (in our case User) and returns this Model again
    * 
    * @param Model $model
    * @return Model
    * @throws \Thettler\LaravelFactoryClasses\Exceptions\FactoryException
    */
    public function create(Model $model): Model
    {
        $relation = $this->relation;
        $relative = $this->convertRelative($this->relatives[0]);
        $model->$relation()->associate($relative)->save();
        return $model;
    }
}

```

## Run tests

```sh
./vendor/bin/phpunit
```

## Author

👤 **Tobias Hettler**

* Website: [stageslice.com](stageslice.com)
* Twitter: [@TobiSlice](https://twitter.com/TobiSlice)
* Github: [@thettler](https://github.com/thettler)

## 🤝 Contributing

Contributions, issues and feature requests are welcome!<br />Feel free to check [issues page](https://github.com/thettler/laravel-factory-classes/issues). 

## Show your support

Give a ⭐️ if this project helped you!

## 📝 License

Copyright © 2020 [Tobias Hettler](https://github.com/thettler).<br />
This project is [MIT](https://choosealicense.com/licenses/mit/) licensed.

***
_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_
