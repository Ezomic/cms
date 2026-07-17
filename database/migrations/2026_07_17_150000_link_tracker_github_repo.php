<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;

// Surgical follow-up to the Tracker case study: the repo is public now.
// Update only github_url (a query-builder update, not the whole seeder) so
// any copy edited in admin since the initial seed is left untouched.
return new class extends Migration
{
    public function up(): void
    {
        Project::where('slug', 'tracker')
            ->update(['github_url' => 'https://github.com/Ezomic/tracker']);
    }

    public function down(): void
    {
        Project::where('slug', 'tracker')->update(['github_url' => null]);
    }
};
