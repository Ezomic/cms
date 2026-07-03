<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : Number of most recent backups to retain}';

    protected $description = 'Copy the SQLite database file to storage/app/backups, pruning older backups';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->error('backup:database only supports the sqlite connection currently in use elsewhere ('.config('database.default').' configured).');

            return self::FAILURE;
        }

        $source = config('database.connections.sqlite.database');

        if (! $source || ! File::exists($source)) {
            $this->error("Database file not found at [{$source}].");

            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $destination = $backupDir.'/backup-'.now()->format('Y-m-d_His').'.sqlite';
        File::copy($source, $destination);

        $this->info("Backup written to {$destination}");

        $this->prune($backupDir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    private function prune(string $backupDir, int $keep): void
    {
        $backups = collect(File::files($backupDir))
            ->filter(fn ($file) => $file->getExtension() === 'sqlite')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $backups->slice($keep)->each(function ($file) {
            File::delete($file->getPathname());
            $this->line("Pruned old backup: {$file->getFilename()}");
        });
    }
}
