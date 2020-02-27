<?php

namespace Thettler\LaravelFactoryClasses\Tests\support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToModel extends Model
{
    public function belongsToOneRelation(): BelongsTo
    {
        return $this->belongsTo(HasOneModel::class, 'has_one_model_id');
    }

    public function belongsToManyRelation(): BelongsToMany
    {
        return $this->belongsToMany(HasOneModel::class, 'many_to_many')->withPivot('pivot');
    }
}
