<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CleanupFreelancerProfileData extends Command
{
    protected $signature = 'freelancer:cleanup-profile-data {--dry-run : Show referenced files without deleting them} {--force : Required to delete files}';

    protected $description = 'Delete files referenced by freelancer profile, portfolio, and certificate records.';

    private const REQUIRED_TABLES = [
        'freelancer_profiles',
        'freelancer_profile_skills',
        'freelancer_work_experiences',
        'freelancer_portfolio_items',
        'freelancer_certificates',
        'freelancer_profile_verification_logs',
    ];

    private const DEFAULT_IMAGE = 'def.png';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $missingTables = $this->missingRequiredTables();

        if ($missingTables) {
            $message = 'Missing freelancer profile table(s): ' . implode(', ', $missingTables);

            if ($dryRun) {
                $this->warn($message);
                $this->warn('Dry-run only: no files were deleted.');
                return self::SUCCESS;
            }

            $this->error($message);
            $this->error('Aborting. Run this command before the destructive migration, or restore the tables from backup.');
            return self::FAILURE;
        }

        if (!$dryRun && !$force) {
            $this->error('Refusing to delete files without --force. Use --dry-run first to review the file list.');
            return self::FAILURE;
        }

        $files = $this->referencedFiles();
        $this->info(($dryRun ? 'Dry-run' : 'Delete') . ' mode: ' . count($files) . ' referenced freelancer profile file(s) found.');

        $deleted = 0;
        $missing = 0;

        foreach ($files as $file) {
            $exists = $this->fileExists($file);

            if ($dryRun) {
                $this->line(($exists ? '[exists] ' : '[missing] ') . $file['disk'] . ':' . $file['path']);
                $missing += $exists ? 0 : 1;
                continue;
            }

            if (!$exists) {
                $missing++;
                $this->warn('[missing] ' . $file['disk'] . ':' . $file['path']);
                continue;
            }

            $this->deleteFile($file);
            $deleted++;
            $this->line('[deleted] ' . $file['disk'] . ':' . $file['path']);
        }

        if (!$dryRun) {
            $this->removeSafeEmptyDirectories($files);
        }

        $this->info('Deleted: ' . $deleted);
        $this->info('Missing: ' . $missing);

        return self::SUCCESS;
    }

    private function missingRequiredTables(): array
    {
        return array_values(array_filter(
            self::REQUIRED_TABLES,
            fn (string $table): bool => !Schema::hasTable($table)
        ));
    }

    private function referencedFiles(): array
    {
        $files = [];

        DB::table('freelancer_profiles')
            ->select(['profile_image', 'profile_image_storage_type', 'cover_image', 'cover_image_storage_type'])
            ->orderBy('id')
            ->chunk(200, function ($profiles) use (&$files): void {
                foreach ($profiles as $profile) {
                    $this->addFile($files, 'freelancer-profile/', $profile->profile_image, $profile->profile_image_storage_type);
                    $this->addFile($files, 'freelancer-cover/', $profile->cover_image, $profile->cover_image_storage_type);
                }
            });

        DB::table('freelancer_portfolio_items')
            ->select(['image', 'image_storage_type'])
            ->orderBy('id')
            ->chunk(200, function ($items) use (&$files): void {
                foreach ($items as $item) {
                    $this->addFile($files, 'freelancer-portfolio/', $item->image, $item->image_storage_type);
                }
            });

        DB::table('freelancer_certificates')
            ->select(['certificate_file', 'certificate_file_storage_type'])
            ->orderBy('id')
            ->chunk(200, function ($certificates) use (&$files): void {
                foreach ($certificates as $certificate) {
                    $this->addFile($files, 'freelancer-certificate/', $certificate->certificate_file, $certificate->certificate_file_storage_type);
                }
            });

        return array_values($files);
    }

    private function addFile(array &$files, string $directory, ?string $name, ?string $disk): void
    {
        if (!$name || $name === self::DEFAULT_IMAGE || str_contains($name, '..')) {
            return;
        }

        $path = $directory . ltrim($name, '/\\');
        $files[($disk ?: 'public') . ':' . $path] = [
            'disk' => $disk ?: 'public',
            'path' => $path,
        ];
    }

    private function fileExists(array $file): bool
    {
        try {
            if (Storage::disk($file['disk'])->exists($file['path'])) {
                return true;
            }
        } catch (Throwable $exception) {
            $this->warn('[storage-check-failed] ' . $file['disk'] . ':' . $file['path'] . ' - ' . $exception->getMessage());
        }

        return file_exists(public_path('storage/' . $file['path']));
    }

    private function deleteFile(array $file): void
    {
        try {
            Storage::disk($file['disk'])->delete($file['path']);
        } catch (Throwable $exception) {
            $this->warn('[storage-delete-failed] ' . $file['disk'] . ':' . $file['path'] . ' - ' . $exception->getMessage());
        }

        $publicPath = public_path('storage/' . $file['path']);
        if (file_exists($publicPath)) {
            @unlink($publicPath);
        }
    }

    private function removeSafeEmptyDirectories(array $files): void
    {
        $directories = collect($files)
            ->pluck('path')
            ->map(fn (string $path): string => trim(dirname($path), '.\\/'))
            ->unique()
            ->sortByDesc(fn (string $path): int => substr_count($path, '/'))
            ->values();

        foreach ($directories as $directory) {
            foreach (array_unique(['public', config('filesystems.default', 'public')]) as $disk) {
                if (!$directory || !Storage::disk($disk)->exists($directory)) {
                    continue;
                }

                if (Storage::disk($disk)->files($directory) === [] && Storage::disk($disk)->directories($directory) === []) {
                    Storage::disk($disk)->deleteDirectory($directory);
                    $this->line('[removed-empty-dir] ' . $disk . ':' . $directory);
                }
            }
        }
    }
}
