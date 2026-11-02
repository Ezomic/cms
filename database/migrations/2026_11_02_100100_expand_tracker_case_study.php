<?php

use App\Models\Project;
use Database\Seeders\TrackerProjectSeeder;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-141): the Tracker case study is rewritten around the
// "self-hosted Linear alternative / Laravel bug tracker" angle. Only the copy
// columns are replaced (name, meta, description, outcome, body, both languages),
// taken from the seeder so fresh installs and production end up identical.
// Ordering, tags, visibility and the GitHub link are left alone. The copy is
// replaced unconditionally, so anything edited in the admin for this project
// since the initial seed is overwritten. Reaches prod via the deploy's migrate
// step. down() is a no-op: the previous copy is not kept.
return new class extends Migration
{
    public function up(): void
    {
        Project::where('slug', 'tracker')->first()?->update((new TrackerProjectSeeder)->copy());
    }

    public function down(): void {}
};
