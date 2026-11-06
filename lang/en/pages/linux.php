<?php

return [
    'meta_title' => 'Linux server setup and self-hosting | R. Thijssen',
    'meta_description' => 'Linux server setup and self-hosting for small businesses and founders: a file server, a server for your tool or site, an old PC as NAS. Remote or in Twente.',
    'service_type' => 'Linux server setup and self-hosting',
    'breadcrumb' => 'Linux servers and self-hosting',
    'footer_link' => 'Linux servers',

    'hero_eyebrow' => 'Linux servers · Self-hosting',
    'hero_headline' => 'Your own server, set up properly and kept in order.',
    'hero_lead' => 'Want your files, your tool or your website on a server you control, instead of renting yet another subscription? I set up Linux servers for small businesses and founders, and I can look after them afterwards if you like. I work as a freelancer from Enschede, remote for the Netherlands and Belgium, and on site in Twente when that helps.',
    'hero_primary' => 'Tell me what you need →',
    'hero_secondary' => 'See case studies',

    'facts' => [
        ['label' => 'Based in', 'value' => 'Enschede, NL'],
        ['label' => 'Works in', 'value' => 'Netherlands and Belgium, remote. On site in Twente'],
        ['label' => 'Runs on', 'value' => 'Debian or Ubuntu Linux'],
        ['label' => 'Proof', 'value' => 'Two public projects and my own server'],
    ],

    'who_label' => 'Why your own server',
    'who_headline' => 'Control over where your data lives.',
    'who_p1' => 'A server of your own means your files and tools sit where you decide, cost the same every month and do not disappear when a provider changes its terms. It only works well when someone sets it up carefully: updates, access, backups and a way to see when something goes wrong.',
    'who_p2' => 'That is the part I take care of. I set it up, write down how it works in plain words and explain what you need to know. Care afterwards is optional: a first job comes first, and I offer to keep looking after the server once it is running, with no commitment up front.',

    'build_label' => 'What I set up',
    'build_headline' => 'Three common jobs, and one for when it has gone wrong.',
    'build_intro' => 'Most clients come with one of these. I use Debian or Ubuntu Linux, nginx, systemd, Docker, Ansible and MySQL, with encrypted backups kept off site.',
    'build_items' => [
        [
            'title' => 'Your own company file server or private cloud',
            'body' => 'A place for the shared files of your team that lives on your own hardware or rented server instead of a storage subscription. Access per person, backups included, reachable from the office and from home.',
        ],
        [
            'title' => 'A server for a tool or website',
            'body' => 'A tool or website running on a server of its own, secured and updated. Moving away from an old host is part of it, and where possible without downtime: the new server runs alongside the old one first, and you switch over when it works.',
        ],
        [
            'title' => 'An old PC turned into a NAS',
            'body' => 'A NAS is a small file server in your home or office. A spare PC in Enschede or Hengelo is enough: shared folders for the family or the team, backups of everyone\'s computers and files you can reach from your phone.',
        ],
        [
            'title' => 'Server in trouble',
            'body' => 'A server that is broken, that nobody manages or that nobody knows how it was built? I first work out what is on it and what is at risk, then say honestly whether to repair it or rebuild it. It works like the stuck-project rescue on the home page.',
        ],
    ],

    'steps_label' => 'How we work',
    'steps_headline' => 'From wish to a server that runs.',
    'steps_intro' => 'Four steps, and I explain each one in plain words.',
    'steps_items' => [
        [
            'title' => 'Intro call',
            'body' => 'A 20-minute call: what should live on the server, who needs access and what is there now?',
        ],
        [
            'title' => 'Plan',
            'body' => 'A short description of what I will set up, how it is secured and where the backups go, before I touch anything.',
        ],
        [
            'title' => 'Setup',
            'body' => 'Built so it can be rebuilt from a written recipe, tested, with your data moved over and checked.',
        ],
        [
            'title' => 'Handover and care',
            'body' => 'You get the documentation and a walkthrough. Care afterwards is an offer: a first job comes first, and it is up to you whether I keep looking after it.',
        ],
    ],

    'proof_label' => 'Proof',
    'proof_headline' => 'Servers I run myself.',
    'proof_intro' => 'These are projects of my own, not client work. The code of the first two is public, so you can see how I work.',
    'proof_items' => [
        'filebox' => [
            'title' => 'filebox: a home file server',
            'body' => 'Turns an old PC into a file server for the family: SFTP, a web portal that works on a phone and an admin panel reachable only over a private network. It comes with an install guide and an update command that can also roll back.',
        ],
        'streambox' => [
            'title' => 'streambox: a media server to go with it',
            'body' => 'A small self-hosted program that serves video files you have the rights to as channels for ordinary TV apps. It runs as one program, restarts a channel when its stream drops and is meant as an add-on to filebox.',
        ],
        'ownserver' => [
            'title' => 'My own production server',
            'body' => 'One machine that runs many applications, each in its own locked-down account. A backup is taken before risky changes, and the whole machine can be rebuilt from code.',
        ],
    ],

    'cta_label' => 'Get in touch',
    'cta_headline' => 'A server to set up, or one that needs rescuing?',
    'cta_lead' => 'Tell me what it has to do, or what has gone wrong. I usually reply within one business day.',
    'cta_primary' => 'Send a message →',
];
