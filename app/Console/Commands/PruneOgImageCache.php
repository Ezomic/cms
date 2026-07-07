<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneOgImageCache extends Command
{
    protected $signature = 'og:prune-cache {--days=30 : Remove OG image cache entries whose embedded timestamp is older than this many days}';

    protected $description = 'Remove stale OG image cache entries from the database cache table';

    public function handle(): int
    {
        // OG image entries accumulate stale keys in the SQLite cache table because they
        // are keyed by the content's updated_at timestamp, e.g. og.home.1234567890 and
        // og.project.{id}.1234567890. When the underlying content is updated the old key
        // is never hit again — it just sits in the cache table forever. Cache::flush()
        // must NOT be used here because it also clears home.page.data and causes a slow
        // cold rebuild on the next public request. We target og.* keys only, and only
        // those whose embedded Unix timestamp is older than the configured threshold.
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days)->getTimestamp();

        $keyPrefix = config('cache.prefix', '').'og.';

        $keys = DB::table('cache')
            ->where('key', 'like', $keyPrefix.'%')
            ->pluck('key');

        $toDelete = $keys->filter(function (string $key) use ($keyPrefix, $cutoff): bool {
            $bare = substr($key, strlen($keyPrefix));
            $parts = explode('.', $bare);
            $ts = (int) end($parts);

            return $ts > 0 && $ts < $cutoff;
        });

        if ($toDelete->isNotEmpty()) {
            DB::table('cache')->whereIn('key', $toDelete->values()->all())->delete();
        }

        $count = $toDelete->count();
        $this->info("Pruned {$count} stale OG image cache entries older than {$days} days.");

        return self::SUCCESS;
    }
}
