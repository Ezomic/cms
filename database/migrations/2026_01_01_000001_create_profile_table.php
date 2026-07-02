<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single-row table holding the site owner's editable info.
        Schema::create('profile', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('[Your Name]');
            $table->string('city')->default('Amsterdam');
            $table->string('tagline')->default('Full-stack Web Developer');
            $table->string('hero_headline')->default('Building web products that work, end to end.');
            $table->text('hero_subtext')->nullable();
            $table->boolean('available')->default(true);
            $table->string('email')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('rate')->nullable();
            $table->string('availability_from')->nullable();
            $table->string('kvk_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile');
    }
};
