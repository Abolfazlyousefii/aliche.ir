<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\PublicFileUrl;

class ChamberMember extends Model
{
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'position', 'photo', 'bio', 'sort_order', 'is_active', 'is_president'];

    protected static function booted(): void
    {
        static::saving(function (self $member) {
            $member->active_president_slot = $member->is_active && $member->is_president ? 1 : null;
        });
    }

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'is_president' => 'boolean'];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getPhotoUrlAttribute(): string
    {
        return PublicFileUrl::make($this->photo);
    }
}
