<?php

namespace Thettler\LaravelFactoryClasses\Tests\support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class HasOneModel extends Model
{

    /**
     * Get the phone record associated with the user.
     */
    public function hasOneRelation(): HasOne
    {
        return $this->hasOne(BelongsToModel::class);
    }

    /**
     * Get the phone record associated with the user.
     */
    public function morphOneRelation(): MorphOne
    {
        return $this->morphOne(MorphToModel::class, 'morphToRelation', 'morph_to_relation_type', 'morph_to_relation_id');
    }

    /**
     * Get the phone record associated with the user.
     */
    public function hasManyRelation(): HasMany
    {
        return $this->hasMany(BelongsToModel::class);
    }
}
