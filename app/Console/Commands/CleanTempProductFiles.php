<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempProductFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-temp-product-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove staged product upload files older than 24 hours.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk('public');
        $tempRoot = 'temp';
        $cutoff = Carbon::now()->subDay()->timestamp;
        $deletedFiles = 0;

        if (!$disk->exists($tempRoot)) {
            $this->info('No staged temp product files found.');
            return self::SUCCESS;
        }

        foreach ($disk->directories($tempRoot) as $directory) {
            foreach ($disk->allFiles($directory) as $file) {
                if ($disk->lastModified($file) < $cutoff) {
                    $disk->delete($file);
                    $deletedFiles++;
                }
            }

            if (empty($disk->allFiles($directory))) {
                $disk->deleteDirectory($directory);
            }
        }

        $this->info("Cleaned {$deletedFiles} staged product file(s).");

        return self::SUCCESS;
    }
}
