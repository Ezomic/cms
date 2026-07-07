<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->string('tagline_nl')->nullable();
            $table->string('hero_headline_nl')->nullable();
            $table->text('hero_subtext_nl')->nullable();
            $table->text('docs_intro_nl')->nullable();
            $table->string('meta_title_nl')->nullable();
            $table->string('meta_description_nl')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->dropColumn(['tagline_nl', 'hero_headline_nl', 'hero_subtext_nl', 'docs_intro_nl', 'meta_title_nl', 'meta_description_nl']);
        });
    }
};
