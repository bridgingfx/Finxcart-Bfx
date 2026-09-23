<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Models\BusinessPage;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Blog\app\Models\Blog;

class SitemapGeneratorService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo,
        private readonly ShopRepositoryInterface $shopRepo,
    )
    {
    }

    public function generate(string $action): ?string
    {
        Artisan::call('file:permission');

        $currentTime = Carbon::now();
        $entries = $this->buildEntries($currentTime);

        $directory = storage_path('app/public/sitemap');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0777, true, true);
        }

        $fileName = 'sitemap-' . Str::slug($currentTime) . '.xml';
        $storagePath = storage_path('app/public/sitemap/' . $fileName);

        $this->writeSitemap($entries, $storagePath);

        if ($action === 'upload') {
            $this->writeSitemap($entries, public_path('sitemap.xml'));
            $this->writeSitemap($entries, base_path('sitemap.xml'));

            return null;
        }

        return dynamicStorage('storage/app/public/sitemap/' . $fileName);
    }

    private function buildEntries(Carbon $currentTime): array
    {
        $entries = [];

        $staticUrls = [
            url('/'),
            route('brands'),
            route('vendors'),
            route('home'),
            route('categories'),
            route('contacts'),
            route('helpTopic'),
            route('products'),
            route('discounted-products'),
            route('track-order.index'),
            route('shopView', ['id' => 0]),
        ];

        foreach ($staticUrls as $url) {
            $this->addEntry($entries, $url, $currentTime);
        }

        $products = $this->productRepo->getWebListWithScope(scope: 'active', dataLimit: 'all');
        foreach ($products as $product) {
            $slug = data_get($product, 'slug');

            if ($slug) {
                $this->addEntry($entries, route('product', ['slug' => $slug]), $currentTime);
            }
        }

        foreach (Blog::active()->get(['slug']) as $blog) {
            if ($blog->slug) {
                $this->addEntry($entries, route('frontend.blog.details', ['slug' => $blog->slug]), $currentTime);
            }
        }

        $shops = $this->shopRepo->getListWithScope(scope: 'active', dataLimit: 'all');
        foreach ($shops as $shop) {
            $shopId = data_get($shop, 'id');

            if ($shopId !== null) {
                $this->addEntry($entries, route('shopView', ['id' => $shopId]), $currentTime);
            }
        }

        foreach (BusinessPage::where('status', 1)->get(['slug']) as $businessPage) {
            if ($businessPage->slug) {
                $this->addEntry($entries, route('business-page.view', ['slug' => $businessPage->slug]), $currentTime);
            }
        }

        return array_values($entries);
    }

    private function addEntry(array &$entries, string $url, Carbon $currentTime): void
    {
        $entries[$url] = [
            'loc' => $url,
            'lastmod' => $currentTime->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
    }

    private function writeSitemap(array $entries, string $path): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $urlset = $dom->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
        $dom->appendChild($urlset);

        foreach ($entries as $entry) {
            $url = $dom->createElement('url');
            $urlset->appendChild($url);

            foreach ($entry as $key => $value) {
                $url->appendChild($dom->createElement($key, $value));
            }
        }

        File::put($path, $dom->saveXML());
    }
}
