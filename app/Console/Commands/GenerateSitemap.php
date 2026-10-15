<?php

namespace App\Console\Commands;

use App\Services\SitemapGeneratorService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate and publish the sitemap files';

    public function __construct(
        private readonly SitemapGeneratorService $sitemapGeneratorService,
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->sitemapGeneratorService->generate('upload');

        $this->info('Sitemap generated successfully.');

        return self::SUCCESS;
    }
}
