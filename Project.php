<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Project extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'projects';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'status',
        'ai_prompt',
        'generated_code',
        'file_structure',
        'dependencies',
        'deployment_url',
        'database_config',
        'ai_models_used',
        'build_logs',
        'is_public',
        'voice_command_used'
    ];

    protected $casts = [
        'generated_code' => 'array',
        'file_structure' => 'array',
        'dependencies' => 'array',
        'database_config' => 'array',
        'ai_models_used' => 'array',
        'build_logs' => 'array',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }
}