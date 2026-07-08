<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin login is passwordless — log in via emailed login code, then register a passkey.
        User::firstOrCreate(
            ['email' => 'robbin_thijssen@hotmail.nl'],
            ['name' => 'Admin']
        );

        Profile::updateOrCreate(['id' => 1], [
            'name' => '[Your Name]',
            'city' => 'Amsterdam',
            'tagline' => 'Full-stack Web Developer',
            'hero_headline' => 'Building web products that work, end to end.',
            'hero_subtext' => 'Freelance full-stack developer based in the Netherlands. I design, build, and ship reliable products for founders and teams — from first line of code to production.',
            'available' => true,
            'email' => 'hello@yourname.dev',
            'linkedin_url' => 'https://linkedin.com/in/yourname',
            'github_url' => 'https://github.com/yourname',
            'rate' => '€XX/hr or fixed',
            'availability_from' => 'next month',
            'kvk_number' => '12345678',
        ]);

        $skills = [
            'Frontend' => ['React / Next.js', 'TypeScript', 'Design systems & accessibility', 'Performance tuning'],
            'Backend' => ['Node.js / Python', 'REST & GraphQL APIs', 'PostgreSQL', 'Auth & payments'],
            'Infra & Ops' => ['Docker & CI/CD', 'AWS / Vercel deployment', 'Monitoring & logging', 'GDPR-aware data handling'],
        ];

        foreach ($skills as $category => $names) {
            foreach ($names as $i => $name) {
                Skill::updateOrCreate(
                    ['category' => $category, 'name' => $name],
                    ['sort_order' => $i]
                );
            }
        }

        Project::updateOrCreate(['name' => 'Project name'], [
            'client_name' => 'Client name',
            'year' => '2025',
            'description' => 'Rebuilt checkout flow, cut load time by 40% and lifted conversion.',
            'tags' => 'Next.js, Stripe',
            'sort_order' => 1,
        ]);

        Project::updateOrCreate(['name' => 'Internal dashboard'], [
            'client_name' => 'Client name',
            'year' => '2024',
            'description' => 'Built internal dashboard replacing three spreadsheets.',
            'tags' => 'React, Node.js, Postgres',
            'sort_order' => 2,
        ]);

        Project::updateOrCreate(['name' => 'MVP launch'], [
            'client_name' => 'Client name',
            'year' => '2024',
            'description' => 'Launched MVP in 6 weeks, from spec to first paying customer.',
            'tags' => 'Vue, Python',
            'sort_order' => 3,
        ]);

        Testimonial::updateOrCreate(['author_name' => 'Name, role @ Company'], [
            'quote' => 'Straightforward, on time, and the code was still readable a year later.',
            'featured' => true,
        ]);
    }
}
