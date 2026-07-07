<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title_nl')->nullable();
            $table->string('excerpt_nl')->nullable();
            $table->longText('body_nl')->nullable();
            $table->string('meta_title_nl')->nullable();
            $table->string('meta_description_nl')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['title_nl', 'excerpt_nl', 'body_nl', 'meta_title_nl', 'meta_description_nl']);
        });
    }
};
