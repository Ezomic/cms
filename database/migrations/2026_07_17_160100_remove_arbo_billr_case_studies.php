<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;

// Content migration (reaches prod via the deploy's migrate step): soft-delete
// the Arbo SaaS and Billr case studies so they no longer appear in the
// portfolio. Reversible — down() restores them from trash. Iterates model
// instances so the deleted/restored events fire (BustsHomeCache).
return new class extends Migration
{
    /** @var list<string> */
    private array $slugs = ['arbo-saas', 'billr'];

    public function up(): void
    {
        Project::whereIn('slug', $this->slugs)->get()->each->delete();
    }

    public function down(): void
    {
        Project::withTrashed()->whereIn('slug', $this->slugs)->get()->each->restore();
    }
};
