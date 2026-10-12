<?php

namespace App\Services;

use App\Http\Controllers\LandingController;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The latest change to anything a public page renders, for the sitemap's
 * <lastmod>. A fixed value (the profile's updated_at) went stale the moment
 * a project or testimonial changed and the profile did not.
 */
class ContentLastModified
{
    public function home(): ?CarbonInterface
    {
        $projects = Project::published()->featured()->exists()
            ? Project::published()->featured()
            : Project::published();

        return $this->latest(
            $this->profile(),
            $this->newest($projects),
            $this->newest(Skill::query()),
            $this->newest(Testimonial::where('featured', true)),
        );
    }

    public function docs(): ?CarbonInterface
    {
        return $this->latest($this->profile(), $this->newest(Skill::query()), $this->newest(Project::published()));
    }

    public function work(): ?CarbonInterface
    {
        return $this->latest($this->newest(Project::published()));
    }

    /**
     * @param  Collection<int, Project>  $projects  every published project
     */
    public function tag(Collection $projects, string $tag): ?CarbonInterface
    {
        return $this->latest(...$projects
            ->filter(fn (Project $project): bool => in_array($tag, $project->tagList(), true))
            ->map(fn (Project $project): mixed => $project->updated_at)
            ->all());
    }

    public function landing(): ?CarbonInterface
    {
        return $this->latest(
            $this->profile(),
            $this->newest(Project::published()->whereIn('slug', LandingController::PROOF_SLUGS)),
            CarbonImmutable::parse(LandingController::COPY_UPDATED_AT),
        );
    }

    private function profile(): mixed
    {
        return Profile::current()->updated_at;
    }

    /**
     * @param  Builder<*>  $query
     */
    private function newest(Builder $query): mixed
    {
        return $query->latest('updated_at')->value('updated_at');
    }

    private function latest(mixed ...$candidates): ?CarbonInterface
    {
        return collect($candidates)
            ->filter(fn (mixed $candidate): bool => $candidate instanceof CarbonInterface)
            ->sort(fn (CarbonInterface $a, CarbonInterface $b): int => $b <=> $a)
            ->first();
    }
}
