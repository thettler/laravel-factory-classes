<?php

namespace Thettler\LaravelFactoryClasses\Tests\support\Models;

use Illuminate\Database\Eloquent\Model;

class SimpleModel extends Model
{
    protected $casts = [
        'publish' => 'bool'
    ];
}
