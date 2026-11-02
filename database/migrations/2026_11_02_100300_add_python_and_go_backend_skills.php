<?php

use App\Models\Skill;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-144, reaches prod via the deploy's migrate step). The
// skills list is database content, so Python and Go are added to whichever
// category is the back-end one, after its existing skills, and only when they
// are not already there. Nothing is added when no back-end category exists.
// The model is used so BustsHomeCache clears the cached home page.
return new class extends Migration
{
    private const NAMES = ['Python', 'Go'];

    public function up(): void
    {
        $category = $this->backendCategory();

        if ($category === null) {
            return;
        }

        $next = (int) Skill::where('category', $category)->max('sort_order') + 1;

        foreach (self::NAMES as $name) {
            if (Skill::withTrashed()->where('category', $category)->where('name', $name)->exists()) {
                continue;
            }

            Skill::create(['category' => $category, 'name' => $name, 'sort_order' => $next++]);
        }
    }

    public function down(): void
    {
        $category = $this->backendCategory();

        if ($category === null) {
            return;
        }

        Skill::withTrashed()
            ->where('category', $category)
            ->whereIn('name', self::NAMES)
            ->get()
            ->each(fn (Skill $skill) => $skill->forceDelete());
    }

    private function backendCategory(): ?string
    {
        $category = Skill::query()
            ->distinct()
            ->pluck('category')
            ->first(fn (string $name) => in_array(strtolower($name), ['backend', 'back-end', 'back end'], true));

        return $category === null ? null : (string) $category;
    }
};
