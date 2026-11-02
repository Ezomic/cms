<?php

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-144, reaches prod via the deploy's migrate step). The
// home lead is a profile column edited in the admin, so each language is a
// compare-and-set: the AI sentence is only appended while the column still
// holds the value the CMS-135 migration left live. A lead that has been edited
// since is left alone; set it by hand at /admin/profile. The model is used so
// BustsHomeCache clears the cached home page.
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string}> column => [live value, new value] */
    private const CHANGES = [
        'hero_subtext' => [
            'Freelance Laravel developer in Enschede with 9+ years of experience, working across Twente and remotely. I specialise in Laravel and Vue.js, building reliable, maintainable web products for clients from SMEs to large enterprises.',
            'Freelance Laravel developer in Enschede with 9+ years of experience, working across Twente and remotely. I specialise in Laravel and Vue.js, building reliable, maintainable web products for clients from SMEs to large enterprises. I work AI-assisted: from idea to a working proof of concept within days, reviewed and tested by me.',
        ],
        'hero_subtext_nl' => [
            'Freelance Laravel-ontwikkelaar uit Enschede met 9+ jaar ervaring, werkzaam in heel Twente en op afstand. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen.',
            'Freelance Laravel-ontwikkelaar uit Enschede met 9+ jaar ervaring, werkzaam in heel Twente en op afstand. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen. Ik werk AI-ondersteund: van idee naar een werkende proof of concept binnen enkele dagen, door mij gereviewd en getest.',
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
