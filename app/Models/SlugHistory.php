<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugHistory extends Model
{
    protected $fillable = ['sluggable_type', 'sluggable_id', 'old_slug', 'new_slug'];
}
