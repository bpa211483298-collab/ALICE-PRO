<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['web_app', 'mobile_app', 'website', 'game', 'ebook']);
            $table->enum('status', ['initializing', 'generating', 'building', 'deploying', 'completed', 'failed']);
            $table->text('ai_prompt');
            $table->json('generated_code')->nullable();
            $table->json('file_structure')->nullable();
            $table->json('dependencies')->nullable();
            $table->string('deployment_url')->nullable();
            $table->json('database_config')->nullable();
            $table->json('ai_models_used')->nullable();
            $table->json('build_logs')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('voice_command_used')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};