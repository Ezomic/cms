<?php

use App\Models\Project;
use Database\Seeders\TrackerProjectSeeder;
use Illuminate\Database\Migrations\Migration;

// Content migration: the deploy pipeline runs `migrate --force` but never
// `db:seed`, so this is how the Tracker case study reaches production. It
// delegates to the idempotent seeder (updateOrCreate on slug) rather than
// duplicating the copy here. Skipped under `testing` so the seeded row does
// not pollute the RefreshDatabase-migrated test database.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        (new TrackerProjectSeeder)->run();
    }

    public function down(): void
    {
        Project::withTrashed()->where('slug', 'tracker')->forceDelete();
    }
};
