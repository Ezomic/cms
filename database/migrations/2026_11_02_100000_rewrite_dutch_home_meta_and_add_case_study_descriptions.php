<?php

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;

// Content migration (CMS-143, reaches prod via the deploy's migrate step). Like
// the CMS-135 one it only writes over a value that still holds what was live,
// and a case study that already has a description of its own is left alone.
// Edit anything else in the admin. The models are used so the home cache is
// busted on save.
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string}> column => [live value, new value] */
    private const PROFILE_CHANGES = [
        'meta_title_nl' => [
            'Laravel-ontwikkelaar in Enschede | Robbin Thijssen',
            'Laravel-ontwikkelaar Enschede en remote | Robbin Thijssen',
        ],
        'meta_description_nl' => [
            'Freelance Laravel-ontwikkelaar in Enschede met 9+ jaar ervaring. Laravel- en Vue.js-webapplicaties voor mkb en grote bedrijven in Twente en op afstand.',
            'Freelance Laravel-ontwikkelaar in Enschede, actief in Twente en remote. Ik bouw en onderhoud Laravel- en Vue.js-applicaties voor mkb en grote bedrijven.',
        ],
    ];

    /** @var array<string, array{meta_description: string, meta_description_nl: string}> */
    private const PROJECT_DESCRIPTIONS = [
        'data-exchange-portal' => [
            'meta_description' => 'Laravel data-exchange portal linking an absence-management platform to HR systems such as Raet: about 500 partners migrated, up to 200,000 records a day.',
            'meta_description_nl' => 'Data-uitwisselingsportaal in Laravel dat verzuimsoftware koppelt aan HR-systemen zoals Raet: zo\'n 500 partners gemigreerd, tot 200.000 records per dag.',
        ],
    ];

    public function up(): void
    {
        $profile = Profile::first();

        if ($profile !== null) {
            $changes = [];

            foreach (self::PROFILE_CHANGES as $column => $values) {
                if ($profile->getAttribute($column) === $values[0]) {
                    $changes[$column] = $values[1];
                }
            }

            if ($changes !== []) {
                $profile->update($changes);
            }
        }

        foreach (self::PROJECT_DESCRIPTIONS as $slug => $descriptions) {
            $project = Project::where('slug', $slug)->first();

            if ($project === null) {
                continue;
            }

            foreach ($descriptions as $column => $value) {
                if (blank($project->getAttribute($column))) {
                    $project->update([$column => $value]);
                }
            }
        }
    }

    public function down(): void
    {
        $profile = Profile::first();

        if ($profile !== null) {
            $changes = [];

            foreach (self::PROFILE_CHANGES as $column => $values) {
                if ($profile->getAttribute($column) === $values[1]) {
                    $changes[$column] = $values[0];
                }
            }

            if ($changes !== []) {
                $profile->update($changes);
            }
        }

        foreach (self::PROJECT_DESCRIPTIONS as $slug => $descriptions) {
            $project = Project::where('slug', $slug)->first();

            foreach ($descriptions as $column => $value) {
                if ($project?->getAttribute($column) === $value) {
                    $project->update([$column => null]);
                }
            }
        }
    }
};
