<?php

return [
    'meta_title' => 'Linux-servers en self-hosting | R. Thijssen',
    'meta_description' => 'Linux-server inrichten en self-hosting voor mkb en startups: bestandsserver, server voor je tool of site, oude pc als NAS. Remote of op locatie in Twente.',
    'service_type' => 'Linux-servers inrichten en self-hosting',
    'breadcrumb' => 'Linux-servers en self-hosting',
    'footer_link' => 'Linux-servers',

    'hero_eyebrow' => 'Linux-servers · Self-hosting',
    'hero_headline' => 'Je eigen server, goed ingericht en op orde gehouden.',
    'hero_lead' => 'Wil je je bestanden, je tool of je website op een server die van jou is, in plaats van weer een abonnement te huren? Ik richt Linux-servers in voor mkb en startups en kan ze daarna ook beheren als je dat wilt. Ik werk als freelancer vanuit Enschede, remote voor Nederland en België en op locatie in Twente als dat helpt.',
    'hero_primary' => 'Vertel wat je nodig hebt →',
    'hero_secondary' => 'Bekijk case studies',

    'facts' => [
        ['label' => 'Gevestigd in', 'value' => 'Enschede, NL'],
        ['label' => 'Werkgebied', 'value' => 'Nederland en België, remote. Op locatie in Twente'],
        ['label' => 'Draait op', 'value' => 'Debian of Ubuntu Linux'],
        ['label' => 'Bewijs', 'value' => 'Twee openbare projecten en mijn eigen server'],
    ],

    'who_label' => 'Waarom een eigen server',
    'who_headline' => 'Zelf bepalen waar je gegevens staan.',
    'who_p1' => 'Met een eigen server staan je bestanden en tools waar jij dat bepaalt, kosten ze elke maand hetzelfde en verdwijnen ze niet als een aanbieder zijn voorwaarden verandert. Het werkt alleen goed als iemand het zorgvuldig inricht: updates, toegang, back-ups en een manier om te zien wanneer er iets misgaat.',
    'who_p2' => 'Dat deel neem ik op me. Ik richt de server in, leg in gewone woorden vast hoe hij werkt en leg uit wat je moet weten. Beheer daarna is optioneel: eerst een eerste opdracht, en als de server draait bied ik aan hem te blijven beheren, zonder verplichting vooraf.',

    'build_label' => 'Wat ik inricht',
    'build_headline' => 'Drie veelvoorkomende klussen, en één voor als het misgegaan is.',
    'build_intro' => 'Meestal kom je met een van deze bij me terecht. Ik werk met Debian of Ubuntu Linux, nginx, systemd, Docker, Ansible en MySQL, met versleutelde back-ups op een andere locatie.',
    'build_items' => [
        [
            'title' => 'Je eigen bedrijfsserver of privécloud',
            'body' => 'Een plek voor de gedeelde bestanden van je team die op je eigen hardware of een gehuurde server staat in plaats van bij een opslagabonnement. Toegang per persoon, back-ups inbegrepen, bereikbaar vanaf kantoor en thuis.',
        ],
        [
            'title' => 'Een server voor een tool of website',
            'body' => 'Een tool of website op een eigen server, beveiligd en up-to-date. Verhuizen van een oude host hoort erbij, en waar mogelijk zonder downtime: de nieuwe server draait eerst naast de oude en je schakelt over als alles werkt.',
        ],
        [
            'title' => 'Een oude pc als NAS',
            'body' => 'Een NAS is een kleine bestandsserver bij je thuis of op kantoor. Een reservepc in Enschede of Hengelo is genoeg: gedeelde mappen voor het gezin of het team, back-ups van ieders computer en bestanden die je vanaf je telefoon bereikt.',
        ],
        [
            'title' => 'Server in nood',
            'body' => 'Een server die kapot is, die niemand beheert of waarvan niemand weet hoe hij gebouwd is? Ik zoek eerst uit wat erop staat en wat er risico loopt, en zeg dan eerlijk of repareren of opnieuw opbouwen beter is. Het werkt zoals de hulp bij een vastgelopen project op de homepage.',
        ],
    ],

    'steps_label' => 'Zo werken we',
    'steps_headline' => 'Van wens naar een server die draait.',
    'steps_intro' => 'Vier stappen, en ik leg elke stap in gewone woorden uit.',
    'steps_items' => [
        [
            'title' => 'Kennismaking',
            'body' => 'Een gesprek van 20 minuten: wat moet er op de server staan, wie heeft toegang nodig en wat is er nu?',
        ],
        [
            'title' => 'Plan',
            'body' => 'Een korte beschrijving van wat ik inricht, hoe het beveiligd wordt en waar de back-ups heen gaan, voordat ik iets aanraak.',
        ],
        [
            'title' => 'Inrichten',
            'body' => 'Zo gebouwd dat het opnieuw opgebouwd kan worden uit een opgeschreven recept, getest, met je gegevens overgezet en gecontroleerd.',
        ],
        [
            'title' => 'Overdracht en beheer',
            'body' => 'Je krijgt de documentatie en een rondleiding. Beheer daarna is een aanbod: eerst een eerste opdracht, en het is aan jou of ik de server blijf beheren.',
        ],
    ],

    'proof_label' => 'Bewijs',
    'proof_headline' => 'Servers die ik zelf beheer.',
    'proof_intro' => 'Dit zijn projecten van mezelf, geen klantwerk. De code van de eerste twee is openbaar, dus je ziet hoe ik werk.',
    'proof_items' => [
        'filebox' => [
            'title' => 'filebox: een bestandsserver voor thuis',
            'body' => 'Maakt van een oude pc een bestandsserver voor het gezin: SFTP, een webportaal dat op de telefoon werkt en een beheerpaneel dat alleen via een afgeschermd privénetwerk bereikbaar is. Met een installatiegids en een updatecommando waarmee je ook kunt terugdraaien.',
        ],
        'streambox' => [
            'title' => 'streambox: een mediaserver als aanvulling',
            'body' => 'Een klein, zelf te hosten programma dat videobestanden waar je de rechten van hebt aanbiedt als kanalen voor gewone tv-apps. Het draait als één programma, start een kanaal opnieuw op als de stream uitvalt en is bedoeld als aanvulling op filebox.',
        ],
        'ownserver' => [
            'title' => 'Mijn eigen productieserver',
            'body' => 'Eén machine waarop veel applicaties draaien, elk in een eigen afgesloten account. Voor risicovolle wijzigingen wordt een back-up gemaakt en de hele machine is opnieuw op te bouwen uit code.',
        ],
    ],

    'cta_label' => 'Neem contact op',
    'cta_headline' => 'Een server inrichten, of een die gered moet worden?',
    'cta_lead' => 'Vertel wat hij moet doen, of wat er misgegaan is. Ik reageer doorgaans binnen één werkdag.',
    'cta_primary' => 'Stuur een bericht →',
];
