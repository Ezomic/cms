<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsPageViews;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    use RecordsPageViews;

    public function index(): View
    {
        $this->recordPageView();

        // Cached as plain arrays rather than raw Eloquent instances: caching
        // model objects directly proved unreliable to unserialize reliably
        // across requests in this environment (__PHP_Incomplete_Class),
        // and caching the full rendered HTML isn't safe either since it
        // would bake one visitor's CSRF token into every other visitor's
        // page. Arrays cache and restore cleanly; we cast them back to
        // stdClass so the view's property access keeps working unchanged.
        //
        // Keyed per locale: translatable fields are resolved to the current
        // locale before caching, so each key holds one language's copy.
        $locale = app()->getLocale();

        $data = Cache::rememberForever("home.page.data.{$locale}", fn (): array => $this->homePageData());

        return view('home', [
            'profile' => (object) $data['profile'],
            'skills' => collect($data['skills'])->map(fn ($items) => collect($items)->map(fn ($s) => (object) $s)),
            'projects' => collect($data['projects'])->map(fn ($p) => (object) $p),
            'testimonials' => collect($data['testimonials'])->map(fn ($t) => (object) $t),
        ]);
    }

    /**
     * @return array{
     *     profile: array<string, mixed>,
     *     skills: array<string, array<int, array<string, mixed>>>,
     *     projects: array<int, array<string, mixed>>,
     *     testimonials: array<int, array<string, mixed>>,
     * }
     */
    private function homePageData(): array
    {
        $profile = Profile::current();

        $skills = [];

        foreach (Skill::ordered()->get()->groupBy('category') as $category => $items) {
            $skills[(string) $category] = $items
                ->map(fn (Skill $skill): array => $this->stringKeyed($skill->toArray()))
                ->values()
                ->all();
        }

        return [
            'profile' => $this->stringKeyed([
                ...$profile->toArray(),
                'tagline' => $profile->localizedTagline(),
                'hero_headline' => $profile->heroHeadline(),
                'hero_subtext' => $profile->heroSubtext(),
                'meta_title' => $profile->metaTitle(),
                'meta_description' => $profile->metaDescription(),
            ]),
            'skills' => $skills,
            'projects' => $this->homeProjects()->map(fn (Project $project): array => $this->stringKeyed([
                ...$project->toArray(),
                'tag_list' => $project->tagList(),
                'image_url' => $project->imageUrl(),
                'description' => $project->localizedDescription(),
                'outcome' => $project->localizedOutcome(),
            ]))->values()->all(),
            'testimonials' => Testimonial::where('featured', true)->latest()->get()->map(fn (Testimonial $testimonial): array => $this->stringKeyed([
                ...$testimonial->toArray(),
                'quote' => $testimonial->localizedQuote(),
            ]))->values()->all(),
        ];
    }

    /**
     * Featured projects for the home page, falling back to every published
     * project when nothing is featured.
     *
     * The fallback is deliberate: an empty featured set is the state every
     * install starts in, and a home page with no work section reads as broken
     * rather than as unconfigured. /work always shows everything regardless.
     *
     * @return Collection<int, Project>
     */
    private function homeProjects(): Collection
    {
        $featured = Project::published()->featured()->ordered()->get();

        return $featured->isNotEmpty()
            ? $featured
            : Project::published()->ordered()->get();
    }

    public function docs(): View
    {
        $this->recordPageView();

        return view('docs', [
            'profile' => Profile::current(),
            'skills' => Skill::ordered()->get()->groupBy('category'),
            'projects' => Project::published()->ordered()->get(),
        ]);
    }

    public function work(): View
    {
        $this->recordPageView();

        $projects = $this->publishedProjects();

        return view('work', [
            'profile' => Profile::current(),
            'projects' => $projects,
            'tags' => $this->tagsFrom($projects),
            'activeTag' => null,
        ]);
    }

    public function workTag(string $tag): View
    {
        $allProjects = $this->publishedProjects();
        $tags = $this->tagsFrom($allProjects);

        abort_unless($tags->contains($tag), 404);

        $this->recordPageView();

        $projects = $allProjects->filter(
            fn (\stdClass $p): bool => in_array($tag, is_array($p->tag_list) ? $p->tag_list : [], true)
        )->values();

        return view('work', [
            'profile' => Profile::current(),
            'projects' => $projects,
            'tags' => $tags,
            'activeTag' => $tag,
        ]);
    }

    /**
     * @return Collection<int, \stdClass>
     */
    private function publishedProjects(): Collection
    {
        return Project::published()->ordered()->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list' => $p->tagList(),
            'image_url' => $p->imageUrl(),
            'image_alt' => $p->imageAlt(),
            'description' => $p->localizedDescription(),
        ]);
    }

    /**
     * @param  Collection<int, \stdClass>  $projects
     * @return Collection<int, string>
     */
    private function tagsFrom(Collection $projects): Collection
    {
        return $projects
            ->flatMap(fn (\stdClass $p): array => is_array($p->tag_list) ? $p->tag_list : [])
            ->filter(fn (mixed $tag): bool => is_string($tag))
            ->unique()
            ->sort()
            ->values();
    }

    public function cv(): Response
    {
        $profile = Profile::current();
        $skills = Skill::ordered()->get()->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($s) => (object) $s->toArray()));
        $projects = Project::published()->ordered()->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list' => $p->tagList(),
            'description' => $p->localizedDescription(),
            'outcome' => $p->localizedOutcome(),
        ]);

        $pdf = Pdf::loadView('cv', compact('profile', 'skills', 'projects'))
            ->setPaper('a4');

        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $fontMetrics->registerFont(['family' => 'Space Grotesk', 'weight' => 'bold', 'style' => 'normal'], resource_path('fonts/SpaceGrotesk-Bold.ttf'));
        $fontMetrics->registerFont(['family' => 'Inter', 'weight' => 'normal', 'style' => 'normal'], resource_path('fonts/Inter-Regular.ttf'));
        $fontMetrics->registerFont(['family' => 'Inter', 'weight' => 'bold', 'style' => 'normal'], resource_path('fonts/Inter-Bold.ttf'));

        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_text(
            497, 812, 'Page {PAGE_NUM} of {PAGE_COUNT}', $fontMetrics->getFont('Inter', 'normal') ?? 'serif', 8, [0.66, 0.66, 0.66]
        );

        // Locale suffix so a Dutch CV downloaded next to the English one does
        // not silently overwrite it in the reader's downloads folder.
        $suffix = app()->getLocale() === 'nl' ? '-cv-nl.pdf' : '-cv.pdf';
        $filename = str($profile->name)->slug()->append($suffix)->toString();

        return $pdf->download($filename);
    }

    public function project(Project $project): View
    {
        abort_unless($project->published, 404);

        $this->recordPageView();

        return view('project', [
            'profile' => Profile::current(),
            'project' => $project,
        ]);
    }

    /**
     * The same case-study page for an unpublished project, reachable only
     * through a signed link from the admin. No page view is recorded: these
     * hits are the author checking their own draft, not an audience.
     */
    public function projectPreview(Project $project): View
    {
        return view('project', [
            'profile' => Profile::current(),
            'project' => $project,
            'preview' => true,
        ]);
    }

    /**
     * @param  array<mixed>  $row
     * @return array<string, mixed>
     */
    private function stringKeyed(array $row): array
    {
        $keyed = [];

        foreach ($row as $key => $value) {
            $keyed[(string) $key] = $value;
        }

        return $keyed;
    }
}
