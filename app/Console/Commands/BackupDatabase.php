<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'app:backup-database {--type=manual : manual atau auto}';
    protected $description = 'Backup database MySQL memakai mysqldump (fitur C.8 - tanpa dependency package pihak ketiga)';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $filename = 'backup-' . now()->format('Ymd-His') . '.sql';
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

        // Deteksi lokasi mysqldump - di Windows (Laragon/XAMPP) biasanya perlu path lengkap
        // jika tidak ada di PATH. Bisa diatur lewat .env: MYSQLDUMP_PATH=C:\laragon\bin\mysql\...\mysqldump.exe
        $mysqldumpBin = env('MYSQLDUMP_PATH', 'mysqldump');

        $command = [
            $mysqldumpBin,
            '--host=' . $config['host'],
            '--port=' . $config['port'],
            '--user=' . $config['username'],
            '--password=' . $config['password'],
            $config['database'],
        ];

        $process = new Process($command);
        $process->setTimeout(300);
        $process->setWorkingDirectory(base_path());

        // Windows: PHP tidak selalu mengisi SystemRoot ke $_ENV (walau ada di
        // getenv()), padahal Winsock butuh variabel ini untuk init koneksi
        // TCP. Tanpa baris ini, mysqldump.exe gagal dengan error 2004
        // "Can't create TCP/IP socket" walau perintah yang sama berjalan
        // normal ketika dijalankan manual langsung di terminal.
        if (PHP_OS_FAMILY === 'Windows' && ($systemRoot = getenv('SystemRoot'))) {
            $process->setEnv(['SystemRoot' => $systemRoot]);
        }

        try {
            $process->mustRun(function ($type, $buffer) use ($filePath) {
                // Tulis output mysqldump langsung ke file .sql
                File::append($filePath, $buffer);
            });

            Backup::create([
                'file_path' => 'backups/' . $filename,
                'type' => $this->option('type'),
                'created_by' => auth()->id(),
                'file_size' => File::exists($filePath) ? File::size($filePath) : null,
            ]);

            $this->info("Backup database berhasil disimpan: {$filename}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup gagal: ' . $e->getMessage());
            $this->error('Pastikan mysqldump terinstall & bisa diakses. Jika di Windows, atur MYSQLDUMP_PATH di .env, contoh:');
            $this->error('MYSQLDUMP_PATH="C:\\laragon\\bin\\mysql\\mysql-8.x\\bin\\mysqldump.exe"');
            return self::FAILURE;
        }
    }
}
