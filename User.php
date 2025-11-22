<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'avatar',
        'subscription_tier',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_verified_at',
        'current_team_id',
        'profile_photo_path',
        'google2fa_secret',
        'google2fa_enabled'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'google2fa_secret',
        'provider_token',
        'provider_refresh_token'
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'api_usage' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'google2fa_enabled' => 'boolean',
        ];
    }


    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function deployments()
    {
        return $this->hasManyThrough(Deployment::class, Project::class);
    }
}