<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class DataExchangePortalProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::updateOrCreate(
            ['slug' => 'data-exchange-portal'],
            [
                'name' => 'Data-Exchange Portal',
                'client_name' => 'Dotweb Cloud (later Visma Verzuim)',
                'year' => '2021-2022',
                'tags' => 'Laravel, PHP, jQuery, Bootstrap',
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
        return 'A data-exchange portal connecting a Dutch absence-management (verzuim) platform to external HR systems, principally Raet. It mapped each partner\'s incoming data into the canonical format our API accepted, migrating roughly 500 Raet-side partners and exchanging object lists of 20,000 to 200,000 records per day.';
    }

    private function outcomeEn(): string
    {
        return 'Live within a week, it became the working integration route for onboarding Raet partners at scale, handling hundreds of thousands of records a day.';
    }

    private function bodyEn(): string
    {
        return <<<'HTML'
        <h2>The challenge</h2>
        <p>At the core was a mapping problem: incoming data arrived in each external system's format and had to be translated into ours before our API would accept it. What made that hard wasn't volume. It was that the data couldn't be trusted to be clean or consistent, and there was little time to build for it.</p>
        <p>Some disagreements were subtle enough to slip past a naive translation. A recurring one was date-range semantics: one system treated a range's end date as exclusive ("up to"), the other as inclusive ("up to and including"). Map it across without accounting for that and every affected record lands off by a day, an error that never throws. It just silently produces a wrong case file. Field-level rules diverged the same way: a field mandatory on our side could be optional or nullable on theirs, and the reverse.</p>
        <p>On top of that, some incoming data was simply incorrect or corrupt, occasionally describing a state that could not legally exist in our own system. So the layer could not just translate faithfully. It had to recognise impossible input and refuse it rather than force it through.</p>
        <p>And it all happened against a moving target. New mapping edge cases kept surfacing while partners were already live in production, which meant fixing the mapping and repairing the data it had already written, directly on the production server, under real customers.</p>
        <h2>What I built</h2>
        <ul>
            <li>The mapping layer that transformed each partner's incoming data into the canonical format our API accepted, designed and taken live in roughly a week under a hard deadline.</li>
            <li>A validation and error-handling layer inside that mapping, reconciling conflicting rules between the two systems (inclusive versus exclusive date ranges, mismatched required and nullable fields) and rejecting corrupt or impossible input instead of letting it corrupt a case file.</li>
            <li>Integration management on top of the pipeline: tooling to configure and oversee the partner integrations and their data exchange as their number grew.</li>
            <li>Full-stack work across this feature and the wider platform, from the Laravel/PHP backend through the jQuery and Bootstrap frontend.</li>
        </ul>
        HTML;
    }

    private function descriptionNl(): string
    {
        return 'Een uitwisselingsportaal dat een Nederlands verzuimplatform koppelde aan externe HR-systemen, met name Raet. Het zette de binnenkomende data van elke partner om naar het canonieke formaat dat onze API accepteerde. Zo\'n 500 Raet-partners zijn ermee gemigreerd, met objectlijsten van 20.000 tot 200.000 records per dag.';
    }

    private function outcomeNl(): string
    {
        return 'Binnen een week live werd het de vaste integratieroute om Raet-partners op schaal aan te sluiten, met honderdduizenden records per dag.';
    }

    private function bodyNl(): string
    {
        return <<<'HTML'
        <h2>De uitdaging</h2>
        <p>De kern was een mappingprobleem: binnenkomende data kwam binnen in het formaat van elk extern systeem en moest naar dat van ons worden vertaald voordat onze API het accepteerde. Het lastige zat niet in het volume, maar in het feit dat de data niet te vertrouwen was op schoonheid of consistentie, en dat er weinig tijd was om ervoor te bouwen.</p>
        <p>Sommige verschillen waren subtiel genoeg om door een naïeve vertaling te glippen. Een terugkerend geval was de betekenis van datumranges: het ene systeem behandelde de einddatum als exclusief ("tot"), het andere als inclusief ("tot en met"). Zet je dat over zonder er rekening mee te houden, dan valt elk betrokken record een dag verkeerd, een fout die nooit een melding geeft. Het levert stilletjes een verkeerd dossier op. Veldregels liepen op dezelfde manier uiteen: een veld dat bij ons verplicht was, kon bij hen optioneel of leeg zijn, en andersom.</p>
        <p>Daarbovenop was sommige binnenkomende data simpelweg onjuist of corrupt, soms met een toestand die in ons eigen systeem helemaal niet kon bestaan. De laag mocht dus niet klakkeloos vertalen. Hij moest onmogelijke invoer herkennen en weigeren in plaats van er doorheen te duwen.</p>
        <p>En dat alles gebeurde op een bewegend doel. Er bleven nieuwe mapping-uitzonderingen opduiken terwijl partners al live in productie stonden, wat betekende dat de mapping werd gefixt en de al weggeschreven data werd hersteld, rechtstreeks op de productieserver, onder echte klanten.</p>
        <h2>Wat ik heb gebouwd</h2>
        <ul>
            <li>De mappinglaag die de binnenkomende data van elke partner omzette naar het canonieke formaat dat onze API accepteerde, ontworpen en in ongeveer een week live gezet onder een strakke deadline.</li>
            <li>Een validatie- en foutafhandelingslaag binnen die mapping, die botsende regels tussen de twee systemen met elkaar verzoende (inclusieve versus exclusieve datumranges, verschillen in verplichte en lege velden) en corrupte of onmogelijke invoer weigerde in plaats van er een dossier mee te vervuilen.</li>
            <li>Integratiebeheer bovenop de pijplijn: tooling om de partnerintegraties en hun data-uitwisseling in te richten en te bewaken naarmate hun aantal groeide.</li>
            <li>Full-stack werk aan deze functionaliteit en het bredere platform, van de Laravel/PHP-backend tot de frontend in jQuery en Bootstrap.</li>
        </ul>
        HTML;
    }
}
