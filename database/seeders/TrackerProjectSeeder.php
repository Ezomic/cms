<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class TrackerProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::updateOrCreate(
            ['slug' => 'tracker'],
            [
                'name' => 'Tracker',
                'client_name' => 'Thijssen Software',
                'year' => '2026',
                'tags' => 'Laravel, Vue 3, TypeScript, Inertia, Tailwind',
                'published' => true,
                'sort_order' => 1,
                'description' => $this->descriptionEn(),
                'outcome' => $this->outcomeEn(),
                'body' => $this->bodyEn(),
                'description_nl' => $this->descriptionNl(),
                'outcome_nl' => $this->outcomeNl(),
                'body_nl' => $this->bodyNl(),
            ],
        );
    }

    private function descriptionEn(): string
    {
        return 'A self-hosted issue tracker built to replace Linear once it hit its free-tier limit. Multiple projects, each with its own key and independent issue numbering, epics and sub-issues, a Kanban board, a token-authenticated API, and a command-line client that turns a new ticket straight into a ready-to-use git branch.';
    }

    private function outcomeEn(): string
    {
        return 'It replaced the paid SaaS entirely and became the day-to-day tracker for every project I run, with ticket creation wired directly into my development workflow.';
    }

    private function bodyEn(): string
    {
        return <<<'HTML'
        <h2>The challenge</h2>
        <p>I was running every project through Linear until it hit the free tier's cap on active issues. The choice was to start paying or to own the tool outright. I took it as a reason to build something I fully controlled: a tracker that fit the way I actually work, held all my projects under one roof, and could be driven from the command line instead of a browser tab.</p>
        <p>The interesting part was never the list of tickets. It was the surrounding structure that keeps a tracker honest: stable per-project identifiers that never reshuffle, epics with sub-issues that can't be nested into a tangle, a status flow that means the same thing everywhere, and an API trustworthy enough to script against. Getting those rules right, and enforcing them consistently across a web UI and a public API, was the real work.</p>
        <h2>What I built</h2>
        <ul>
            <li>A multi-project tracker where each project carries its own key and independent numbering, so issues get durable identifiers like <code>CMS-71</code> that stay put as everything else moves around them.</li>
            <li>The full issue model: types, priorities, a shared status flow (backlog, in progress, in review, done), labels, and one-level epics with sub-issues, with validation that refuses illegal shapes such as an issue becoming its own epic or crossing projects.</li>
            <li>A Kanban board, filtering, and per-project views, built with Inertia and Vue 3 in TypeScript on a Laravel backend, with Tailwind and shadcn-vue for the interface.</li>
            <li>A token-authenticated JSON API (Laravel Sanctum) covering the issue lifecycle, and a command-line client that creates a ticket and hands back a ready-to-use git branch name, so opening a ticket and starting work are a single step.</li>
            <li>GitHub integration via signed webhooks: pull-request events are matched to their issue and reflected back on the ticket, tying code and tracker together without manual bookkeeping.</li>
            <li>Team plumbing around it: project membership and roles, email invitations, CSV import and export, a dashboard, and a scheduled job that archives finished issues to keep the active board clean.</li>
            <li>Held to the same engineering bar as the rest of my work: Pest feature tests, static analysis with PHPStan, and a consistent code style enforced by Pint.</li>
        </ul>
        HTML;
    }

    private function descriptionNl(): string
    {
        return 'Een zelf-gehoste issue tracker, gebouwd om Linear te vervangen toen dat tegen de limiet van het gratis abonnement aanliep. Meerdere projecten, elk met een eigen sleutel en onafhankelijke nummering, epics en sub-issues, een Kanban-bord, een API met tokenauthenticatie, en een command-line client die een nieuw ticket meteen omzet in een klaar-voor-gebruik git-branch.';
    }

    private function outcomeNl(): string
    {
        return 'Het verving de betaalde SaaS volledig en werd de dagelijkse tracker voor elk project dat ik draai, met het aanmaken van tickets rechtstreeks verweven in mijn ontwikkelworkflow.';
    }

    private function bodyNl(): string
    {
        return <<<'HTML'
        <h2>De uitdaging</h2>
        <p>Ik draaide al mijn projecten via Linear, totdat dat tegen de limiet op actieve issues van het gratis abonnement aanliep. De keuze was gaan betalen of de tool zelf in handen nemen. Ik pakte het aan als reden om iets te bouwen dat ik volledig zelf beheerde: een tracker die paste bij hoe ik echt werk, al mijn projecten onder één dak hield, en vanaf de command line te bedienen was in plaats van vanuit een browsertab.</p>
        <p>Het interessante zat nooit in de lijst met tickets, maar in de structuur eromheen die een tracker eerlijk houdt: stabiele identifiers per project die nooit verschuiven, epics met sub-issues die niet tot een wirwar kunnen ontsporen, een statusflow die overal hetzelfde betekent, en een API die betrouwbaar genoeg is om tegenaan te scripten. Die regels goed krijgen, en ze consistent afdwingen over zowel een web-UI als een publieke API, was het echte werk.</p>
        <h2>Wat ik heb gebouwd</h2>
        <ul>
            <li>Een tracker voor meerdere projecten, waarbij elk project een eigen sleutel en onafhankelijke nummering heeft, zodat issues duurzame identifiers als <code>CMS-71</code> krijgen die op hun plek blijven terwijl de rest eromheen beweegt.</li>
            <li>Het volledige issue-model: types, prioriteiten, een gedeelde statusflow (backlog, in behandeling, in review, klaar), labels, en epics met sub-issues van één niveau diep, met validatie die onmogelijke vormen weigert, zoals een issue dat zijn eigen epic wordt of over projecten heen springt.</li>
            <li>Een Kanban-bord, filtering en weergaven per project, gebouwd met Inertia en Vue 3 in TypeScript op een Laravel-backend, met Tailwind en shadcn-vue voor de interface.</li>
            <li>Een JSON-API met tokenauthenticatie (Laravel Sanctum) die de levenscyclus van een issue afdekt, en een command-line client die een ticket aanmaakt en meteen een klaar-voor-gebruik git-branchnaam teruggeeft, zodat een ticket openen en beginnen met werken één handeling zijn.</li>
            <li>GitHub-integratie via ondertekende webhooks: pull-request-gebeurtenissen worden aan hun issue gekoppeld en op het ticket teruggekaatst, waardoor code en tracker verbonden blijven zonder handmatig bijhouden.</li>
            <li>Het teamwerk eromheen: projectlidmaatschap en rollen, e-mailuitnodigingen, CSV-import en -export, een dashboard, en een geplande taak die afgeronde issues archiveert om het actieve bord schoon te houden.</li>
            <li>Gebouwd op dezelfde kwaliteitslat als de rest van mijn werk: Pest feature-tests, statische analyse met PHPStan, en een consistente codestijl afgedwongen met Pint.</li>
        </ul>
        HTML;
    }
}
