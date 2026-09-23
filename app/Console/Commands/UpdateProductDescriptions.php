<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Rap2hpoutre\FastExcel\FastExcel;

class UpdateProductDescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-descriptions
        {file : Path to a CSV/XLSX file with "name" and "description" columns (optional "category" and/or "id" columns to disambiguate duplicate names)}
        {--dry-run : Preview the matches without saving any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk-update product descriptions from a CSV/XLSX file, matching products by name (optionally narrowed by category or exact id).';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        try {
            $rows = (new FastExcel)->import($path);
        } catch (\Throwable $exception) {
            $this->error('Could not read the file: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $notFound = [];
        $ambiguous = [];

        foreach ($rows as $row) {
            $normalized = collect($row)->mapWithKeys(fn ($value, $key) => [strtolower(trim((string) $key)) => $value]);
            $name = trim((string) ($normalized['name'] ?? ''));
            $description = trim((string) ($normalized['description'] ?? ''));
            $category = trim((string) ($normalized['category'] ?? ''));
            $id = trim((string) ($normalized['id'] ?? ''));

            if ($description === '' || ($name === '' && $id === '')) {
                continue;
            }

            if ($id !== '') {
                $matches = Product::where('id', $id)->get(['id', 'name']);

                if ($matches->isEmpty()) {
                    $notFound[] = "id {$id}";
                    continue;
                }

                if (!$dryRun) {
                    Product::where('id', $matches->first()->id)->update(['details' => $description]);
                }

                $updated++;
                continue;
            }

            $matches = Product::where('name', $name)->with('categories:id,name')->get(['id', 'name']);

            if ($matches->isEmpty()) {
                $notFound[] = $name;
                continue;
            }

            if ($matches->count() > 1 && $category !== '') {
                $narrowed = $matches->filter(
                    fn ($product) => $product->categories->contains(fn ($c) => strcasecmp($c->name, $category) === 0)
                );

                if ($narrowed->count() === 1) {
                    $matches = $narrowed->values();
                }
            }

            if ($matches->count() > 1) {
                $label = $category !== '' ? "{$name} (category: {$category})" : $name;
                $ambiguous[$label] = $matches->map(
                    fn ($product) => $product->id . ' [' . $product->categories->pluck('name')->implode(', ') . ']'
                )->all();
                continue;
            }

            if (!$dryRun) {
                Product::where('id', $matches->first()->id)->update(['details' => $description]);
            }

            $updated++;
        }

        if (!$dryRun && $updated > 0) {
            cacheRemoveByType(type: 'products');
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Matched and updated: {$updated}");

        if (!empty($notFound)) {
            $this->warn('Not found (' . count($notFound) . '):');
            foreach ($notFound as $name) {
                $this->line("  - {$name}");
            }
        }

        if (!empty($ambiguous)) {
            $this->warn('Ambiguous - multiple products share this name, skipped (' . count($ambiguous) . '):');
            $this->line('  Add a "category" column value to narrow it down, or an "id" column with the exact product ID shown below, then re-run.');
            foreach ($ambiguous as $name => $matches) {
                $this->line("  - \"{$name}\" -> " . implode(' | ', $matches));
            }
        }

        return self::SUCCESS;
    }
}
