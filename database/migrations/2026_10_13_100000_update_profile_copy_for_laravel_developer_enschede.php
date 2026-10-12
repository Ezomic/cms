<?php

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-135, reaches prod via the deploy's migrate step). The
// title, description, eyebrow and lead are profile columns edited in the admin,
// so each one is a compare-and-set: it is only replaced while it still holds
// the value that was live on 2026-10-13. A field that has been edited since is
// left alone, and the admin (/admin/profile) is the place to set it by hand.
// The model is used so BustsHomeCache clears the cached home page.
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string}> column => [live value, new value] */
    private const CHANGES = [
        'tagline' => ['Full-Stack Web Developer', 'Freelance Laravel Developer'],
        'meta_title' => ['Robbin Thijssen, full-stack web developer in Enschede', 'Laravel developer in Enschede | Robbin Thijssen'],
        'meta_description' => [
            'Full-stack web developer in Enschede with 9+ years of experience, building reliable Laravel and Vue.js web products for SMEs and enterprises.',
            'Freelance Laravel developer in Enschede with 9+ years of experience. Laravel and Vue.js web applications for SMEs and enterprises in Twente and remote.',
        ],
        'hero_subtext' => [
            'Full-stack web developer based in Enschede with 9+ years of experience. I specialise in Laravel and Vue.js, building reliable, maintainable web products for clients from SMEs to large enterprises.',
            'Freelance Laravel developer in Enschede with 9+ years of experience, working across Twente and remotely. I specialise in Laravel and Vue.js, building reliable, maintainable web products for clients from SMEs to large enterprises.',
        ],
        'tagline_nl' => ['Full-stack webdeveloper', 'Freelance Laravel-ontwikkelaar'],
        'meta_title_nl' => ['Robbin Thijssen, full-stack webdeveloper in Enschede', 'Laravel-ontwikkelaar in Enschede | Robbin Thijssen'],
        'meta_description_nl' => [
            'Full-stack webdeveloper in Enschede met 9+ jaar ervaring, bouwt betrouwbare Laravel- en Vue.js-webproducten voor mkb en grote ondernemingen.',
            'Freelance Laravel-ontwikkelaar in Enschede met 9+ jaar ervaring. Laravel- en Vue.js-webapplicaties voor mkb en grote bedrijven in Twente en op afstand.',
        ],
        'hero_subtext_nl' => [
            'Full-stack webdeveloper uit Enschede met 9+ jaar ervaring. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen.',
            'Freelance Laravel-ontwikkelaar uit Enschede met 9+ jaar ervaring, werkzaam in heel Twente en op afstand. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen.',
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
