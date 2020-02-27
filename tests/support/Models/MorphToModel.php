<?php

namespace Thettler\LaravelFactoryClasses\Tests\support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MorphToModel extends Model
{
    protected $guarded = [];

    /**
     * Get the phone record associated with the user.
     */
    public function morphToRelation(): MorphTo
    {
        return $this->morphTo(HasOneModel::class, 'morph_to_relation_type', 'morph_to_relation_id');
    }
}
