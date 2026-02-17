<?php

declare(strict_types=1);

namespace Frolax\Typescript\Tests\Fixtures\Models;

use Frolax\Typescript\Tests\Fixtures\Enums\UserRole;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'settings',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'is_admin' => 'boolean',
            'balance' => 'decimal:2',
            'role' => UserRole::class,
        ];
    }

    /**
     * New-style accessor.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->name . ' (user)',
        );
    }

    /**
     * Traditional accessor.
     */
    public function getDisplayNameAttribute(): string
    {
        return strtoupper($this->name);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
