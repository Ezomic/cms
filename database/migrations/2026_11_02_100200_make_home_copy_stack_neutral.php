<?php

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-144, reaches prod via the deploy's migrate step). The
// home page is positioned as a stack-neutral software developer; Laravel stays
// on the landing pages, in the skills list and in the case-study tags. Each
// profile column is a compare-and-set: it is only replaced while it still holds
// the value the CMS-135 or CMS-143 migration left live. A column edited since
// is left alone; set it by hand at /admin/profile. hero_headline is not touched
// because its live value is not recorded in the repo. The model is used so
// BustsHomeCache clears the cached home page.
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string}> column => [live value, new value] */
    private const CHANGES = [
        'tagline' => ['Freelance Laravel Developer', 'Freelance software developer'],
        'meta_title' => [
            'Laravel developer in Enschede | Robbin Thijssen',
            'Software developer in Enschede, from idea to working prototype | Robbin Thijssen',
        ],
        'meta_description' => [
            'Freelance Laravel developer in Enschede with 9+ years of experience. Laravel and Vue.js web applications for SMEs and enterprises in Twente and remote.',
            'Freelance software developer in Enschede and remote. From idea to a working prototype within days, then built out into reliable software.',
        ],
        'hero_subtext' => [
            'Freelance Laravel developer in Enschede with 9+ years of experience, working across Twente and remotely. I specialise in Laravel and Vue.js, building reliable, maintainable web products for clients from SMEs to large enterprises.',
            'Freelance software developer in Enschede with 9+ years of experience, working across Twente and remotely. I build reliable software for companies from SMEs to large enterprises. With AI assistants, a working prototype of the most important part can be ready within days, checked and tested by me.',
        ],
        'tagline_nl' => ['Freelance Laravel-ontwikkelaar', 'Freelance software-ontwikkelaar'],
        'meta_title_nl' => [
            'Laravel-ontwikkelaar Enschede en remote | Robbin Thijssen',
            'Software-ontwikkelaar in Enschede, van idee naar werkend prototype | Robbin Thijssen',
        ],
        'meta_description_nl' => [
            'Freelance Laravel-ontwikkelaar in Enschede, actief in Twente en remote. Ik bouw en onderhoud Laravel- en Vue.js-applicaties voor mkb en grote bedrijven.',
            'Freelance software-ontwikkelaar in Enschede en remote. Van idee naar werkend prototype in enkele dagen, daarna doorgebouwd tot betrouwbare software.',
        ],
        'hero_subtext_nl' => [
            'Freelance Laravel-ontwikkelaar uit Enschede met 9+ jaar ervaring, werkzaam in heel Twente en op afstand. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen.',
            'Freelance software-ontwikkelaar uit Enschede met 9+ jaar ervaring, werkzaam in heel Twente en op afstand. Ik bouw betrouwbare software voor bedrijven van mkb tot grote ondernemingen. Met AI-assistenten staat er binnen enkele dagen een werkend prototype van het belangrijkste onderdeel, door mij nagelopen en getest.',
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
