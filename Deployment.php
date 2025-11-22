<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Deployment extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'deployments';

    protected $fillable = [
        'project_id',
        'user_id',
        'platform',
        'status',
        'url',
        'logs',
        'commit_hash',
        'build_time',
        'environment'
    ];

    protected $casts = [
        'logs' => 'array',
        'build_time' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}