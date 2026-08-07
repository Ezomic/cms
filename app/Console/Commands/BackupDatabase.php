<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : Number of most recent backups to retain}';

    protected $description = 'Back up the database to storage/app/backups, pruning older backups';

    /**
     * Local development runs SQLite and production runs MySQL, so this handles
     * both rather than assuming the local shape (CMS-111).
     */
    public function handle(): int
    {
        $connection = config('database.default');
        $driver = config('database.connections.'.(is_string($connection) ? $connection : '').'.driver');

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $status = match ($driver) {
            'sqlite' => $this->backupSqlite($backupDir),
            'mysql', 'mariadb' => $this->backupMysql($backupDir, is_string($connection) ? $connection : 'mysql'),
            default => $this->unsupported($driver),
        };

        if ($status === self::SUCCESS) {
            $this->prune($backupDir, (int) $this->option('keep'));
        }

        return $status;
    }

    private function unsupported(mixed $driver): int
    {
        $this->error('backup:database supports sqlite and mysql only ('.(is_string($driver) ? $driver : 'unknown').' configured).');

        return self::FAILURE;
    }

    private function backupSqlite(string $backupDir): int
    {
        $source = config('database.connections.sqlite.database');

        if (! is_string($source) || $source === '' || ! File::exists($source)) {
            $this->error('Database file not found at ['.(is_string($source) ? $source : '').'].');

            return self::FAILURE;
        }

        $destination = $backupDir.'/backup-'.now()->format('Y-m-d_His').'.sqlite';
        File::copy($source, $destination);

        $this->info("Backup written to {$destination}");

        return self::SUCCESS;
    }

    /**
     * Credentials go through a 0600 defaults file rather than the command line,
     * where they would be visible to anyone running ps on a shared host.
     */
    private function backupMysql(string $backupDir, string $connection): int
    {
        $config = config('database.connections.'.$connection);

        if (! is_array($config)) {
            $this->error("No configuration found for the [{$connection}] connection.");

            return self::FAILURE;
        }

        $database = is_string($config['database'] ?? null) ? $config['database'] : '';

        if ($database === '') {
            $this->error('No database name configured.');

            return self::FAILURE;
        }

        $defaultsFile = $backupDir.'/.my.cnf.'.bin2hex(random_bytes(8));
        $destination = $backupDir.'/backup-'.now()->format('Y-m-d_His').'.sql';

        File::put($defaultsFile, $this->defaultsFileContents($config));
        chmod($defaultsFile, 0600);

        try {
            $dump = Process::timeout(600)->run([
                'mysqldump',
                '--defaults-extra-file='.$defaultsFile,
                '--single-transaction',
                '--quick',
                // Avoids needing the PROCESS privilege, which the app user lacks.
                '--no-tablespaces',
                '--result-file='.$destination,
                $database,
            ]);

            if ($dump->failed()) {
                File::delete($destination);
                $this->error('mysqldump failed: '.trim($dump->errorOutput() ?: $dump->output()));

                return self::FAILURE;
            }
        } finally {
            File::delete($defaultsFile);
        }

        $gzip = Process::timeout(600)->run(['gzip', '--force', $destination]);

        if ($gzip->failed()) {
            $this->error('gzip failed: '.trim($gzip->errorOutput() ?: $gzip->output()));

            return self::FAILURE;
        }

        $this->info("Backup written to {$destination}.gz");

        return self::SUCCESS;
    }

    /**
     * @param  array<mixed>  $config
     */
    private function defaultsFileContents(array $config): string
    {
        $host = is_string($config['host'] ?? null) ? $config['host'] : '127.0.0.1';
        $port = is_scalar($config['port'] ?? null) ? (string) $config['port'] : '3306';
        $username = is_string($config['username'] ?? null) ? $config['username'] : '';
        $password = is_string($config['password'] ?? null) ? $config['password'] : '';

        // MySQL option files process backslash escapes inside double quotes.
        $quoted = '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $password).'"';

        return "[client]\nhost={$host}\nport={$port}\nuser={$username}\npassword={$quoted}\n";
    }

    private function prune(string $backupDir, int $keep): void
    {
        $backups = collect(File::files($backupDir))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), 'backup-'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $backups->slice($keep)->each(function ($file) {
            File::delete($file->getPathname());
            $this->line("Pruned old backup: {$file->getFilename()}");
        });
    }
}
