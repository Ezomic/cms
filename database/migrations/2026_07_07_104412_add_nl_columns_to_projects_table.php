<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('description_nl')->nullable();
            $table->string('outcome_nl')->nullable();
            $table->text('body_nl')->nullable();
            $table->string('image_alt_nl')->nullable();
            $table->string('meta_title_nl')->nullable();
            $table->string('meta_description_nl')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['description_nl', 'outcome_nl', 'body_nl', 'image_alt_nl', 'meta_title_nl', 'meta_description_nl']);
        });
    }
};
