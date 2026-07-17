<?php

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;

// Content migration (reaches prod via the deploy's migrate step): the profile
// day rate was "€500 / day"; drop it to €400. A targeted string replace so it
// only touches the number and preserves whatever surrounding format the rate
// carries. Uses the model so BustsHomeCache clears the cached home page.
return new class extends Migration
{
    public function up(): void
    {
        $this->replaceRate('500', '400');
    }

    public function down(): void
    {
        $this->replaceRate('400', '500');
    }

    private function replaceRate(string $from, string $to): void
    {
        $profile = Profile::first();

        if ($profile !== null && str_contains((string) $profile->rate, $from)) {
            $profile->update(['rate' => str_replace($from, $to, (string) $profile->rate)]);
        }
    }
};
