<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'mobile',
        'is_active',
        'union_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function union(): BelongsTo
    {
        return $this->belongsTo(GuildUnion::class, 'union_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'sender_id');
    }

    public function createdGalleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'created_by');
    }

    public function approvedGalleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'approved_by');
    }

    public function createdVideos(): HasMany
    {
        return $this->hasMany(Video::class, 'created_by');
    }

    public function approvedVideos(): HasMany
    {
        return $this->hasMany(Video::class, 'approved_by');
    }

    public function createdTourismPlaces(): HasMany
    {
        return $this->hasMany(TourismPlace::class, 'created_by');
    }

    public function approvedTourismPlaces(): HasMany
    {
        return $this->hasMany(TourismPlace::class, 'approved_by');
    }

    public function createdElectronicServices(): HasMany
    {
        return $this->hasMany(ElectronicService::class, 'created_by');
    }

    public function approvedElectronicServices(): HasMany
    {
        return $this->hasMany(ElectronicService::class, 'approved_by');
    }

    public function createdCongratulationMessages(): HasMany
    {
        return $this->hasMany(CongratulationMessage::class, 'created_by');
    }

    public function approvedCongratulationMessages(): HasMany
    {
        return $this->hasMany(CongratulationMessage::class, 'approved_by');
    }

    public function hasRole(string|Role $role): bool
    {
        $roleName = $role instanceof Role ? $role->name : $role;

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string|Permission $permission): bool
    {
        $permissionName = $permission instanceof Permission ? $permission->name : $permission;

        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permissionName))
            ->exists();
    }

    /**
     * @param array<int, string|Permission> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $permissionNames = collect($permissions)
            ->map(fn (string|Permission $permission) => $permission instanceof Permission ? $permission->name : $permission)
            ->filter()
            ->values()
            ->all();

        if ($permissionNames === []) {
            return false;
        }

        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->whereIn('name', $permissionNames))
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
