<?php

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-150, reaches prod via the deploy's migrate step). The
// home H1 is profile data, so CMS-144 left it. Each column is a compare-and-set:
// it is only replaced while it still holds the value live today, so a headline
// edited at /admin/profile since is left alone. The model is used so
// BustsHomeCache clears the cached home page.
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string}> column => [live value, new value] */
    private const CHANGES = [
        'hero_headline' => [
            'Building web applications that work, end to end.',
            'From idea to working software.',
        ],
        'hero_headline_nl' => [
            'Webapplicaties die werken, van begin tot eind.',
            'Van idee naar werkende software.',
        ],
    ];

    public function up(): void
    {
        $this->swap(0, 1);
    }

    public function down(): void
    {
        $this->swap(1, 0);
    }

    private function swap(int $from, int $to): void
    {
        $profile = Profile::first();

        if ($profile === null) {
            return;
        }

        $changes = [];

        foreach (self::CHANGES as $column => $values) {
            if ($profile->getAttribute($column) === $values[$from]) {
                $changes[$column] = $values[$to];
            }
        }

        if ($changes !== []) {
            $profile->update($changes);
        }
    }
};
