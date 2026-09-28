<?php

namespace Tests\Feature;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    private string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDir = storage_path('app/backups');
        File::deleteDirectory($this->backupDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDir);

        parent::tearDown();
    }

    private function useMysql(string $socket = ''): void
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'cms',
            'username' => 'cms_user',
            'password' => 's3cr#t"pass\\word',
            'unix_socket' => $socket,
        ]);
    }

    /**
     * The command deletes the defaults file once mysqldump returns, so it is
     * read while the faked mysqldump call is still running.
     */
    private function defaultsFileWrittenForTheDump(): string
    {
        $contents = '';

        Process::fake(function (PendingProcess $process) use (&$contents) {
            foreach (is_array($process->command) ? $process->command : [] as $argument) {
                if (str_starts_with($argument, '--defaults-extra-file=')) {
                    $contents = File::get(substr($argument, strlen('--defaults-extra-file=')));
                }
            }

            return Process::result();
        });

        $this->artisan('backup:database')->assertExitCode(0);

        return $contents;
    }

    public function test_it_dumps_and_compresses_on_mysql(): void
    {
        $this->useMysql();
        Process::fake();

        $this->artisan('backup:database')->assertExitCode(0);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command)
                && $command[0] === 'mysqldump'
                && in_array('--single-transaction', $command, true)
                && in_array('--no-tablespaces', $command, true)
                && in_array('cms', $command, true);
        });

        Process::assertRan(fn ($process) => is_array($process->command) && $process->command[0] === 'gzip');
    }

    public function test_the_password_never_reaches_the_command_line(): void
    {
        $this->useMysql();
        Process::fake();

        $this->artisan('backup:database')->assertExitCode(0);

        Process::assertRan(function ($process) {
            $flat = implode(' ', is_array($process->command) ? $process->command : [(string) $process->command]);

            $this->assertStringNotContainsString('s3cr#t', $flat, 'password leaked into the command line');
            $this->assertStringNotContainsString('cms_user', $flat, 'username leaked into the command line');

            return true;
        });
    }

    public function test_the_credentials_file_is_removed_afterwards(): void
    {
        $this->useMysql();
        Process::fake();

        $this->artisan('backup:database')->assertExitCode(0);

        $leftovers = collect(File::files($this->backupDir))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), '.my.cnf'));

        $this->assertCount(0, $leftovers, 'the mysql defaults file was left behind');
    }

    public function test_it_fails_when_mysqldump_fails(): void
    {
        $this->useMysql();
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'Access denied for user', exitCode: 1),
        ]);

        $this->artisan('backup:database')
            ->expectsOutputToContain('mysqldump failed')
            ->assertExitCode(1);
    }

    public function test_the_credentials_file_is_removed_even_when_the_dump_fails(): void
    {
        $this->useMysql();
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'boom', exitCode: 1),
        ]);

        $this->artisan('backup:database')->assertExitCode(1);

        $leftovers = collect(File::files($this->backupDir))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), '.my.cnf'));

        $this->assertCount(0, $leftovers);
    }

    public function test_the_defaults_file_uses_the_socket_when_one_is_configured(): void
    {
        $this->useMysql(socket: '/run/mysqld/mysqld.sock');

        $defaults = $this->defaultsFileWrittenForTheDump();

        $this->assertStringContainsString("\nsocket=/run/mysqld/mysqld.sock\n", $defaults);
        $this->assertStringNotContainsString('host=', $defaults, 'a host line would send mysqldump over TCP');
        $this->assertStringNotContainsString('port=', $defaults);
        $this->assertStringContainsString("\nuser=cms_user\n", $defaults);
    }

    public function test_the_defaults_file_uses_host_and_port_without_a_socket(): void
    {
        $this->useMysql();

        $defaults = $this->defaultsFileWrittenForTheDump();

        $this->assertStringContainsString("\nhost=127.0.0.1\nport=3306\n", $defaults);
        $this->assertStringNotContainsString('socket=', $defaults);
        $this->assertStringContainsString("\nuser=cms_user\n", $defaults);
    }

    public function test_it_still_backs_up_sqlite(): void
    {
        $source = storage_path('app/backup-source-test.sqlite');
        File::put($source, 'fake-sqlite-contents');

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.driver', 'sqlite');
        Config::set('database.connections.sqlite.database', $source);

        $this->artisan('backup:database')->assertExitCode(0);

        $backups = collect(File::files($this->backupDir))
            ->filter(fn ($file) => $file->getExtension() === 'sqlite');

        $this->assertCount(1, $backups);

        File::delete($source);
    }

    public function test_it_rejects_an_unsupported_driver(): void
    {
        Config::set('database.default', 'pgsql');
        Config::set('database.connections.pgsql.driver', 'pgsql');

        $this->artisan('backup:database')
            ->expectsOutputToContain('supports sqlite and mysql only')
            ->assertExitCode(1);
    }

    public function test_it_prunes_beyond_the_keep_limit_across_formats(): void
    {
        File::ensureDirectoryExists($this->backupDir);

        foreach (['a.sqlite', 'b.sql.gz', 'c.sqlite', 'd.sql.gz'] as $i => $name) {
            $path = $this->backupDir.'/backup-'.$name;
            File::put($path, 'x');
            touch($path, now()->subDays($i + 1)->getTimestamp());
        }

        $source = storage_path('app/backup-source-test.sqlite');
        File::put($source, 'fake');
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.driver', 'sqlite');
        Config::set('database.connections.sqlite.database', $source);

        $this->artisan('backup:database --keep=2')->assertExitCode(0);

        $remaining = collect(File::files($this->backupDir))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), 'backup-'));

        $this->assertCount(2, $remaining, 'pruning should count both .sqlite and .sql.gz backups');

        File::delete($source);
    }
}
