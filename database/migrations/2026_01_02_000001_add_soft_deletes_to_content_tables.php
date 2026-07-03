<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('skills', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('testimonials', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('skills', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
