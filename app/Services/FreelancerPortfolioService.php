<?php

namespace App\Services;

use App\Models\FreelancerPortfolioGallery;
use App\Models\FreelancerPortfolioItem;
use App\Traits\FileManagerTrait;

class FreelancerPortfolioService
{
    use FileManagerTrait;

    private const IMAGE_DIR = 'freelancer-portfolio/';

    public function getAddData(object $request, int $sellerId, int $priority): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $image = $request->hasFile('image') ? $this->upload(self::IMAGE_DIR, 'webp', $request->file('image')) : null;

        // Only one portfolio item can be active at a time, so a new item only
        // auto-activates when the seller doesn't already have one in use.
        $hasActiveItem = FreelancerPortfolioItem::where('seller_id', $sellerId)->where('is_active', true)->exists();

        return [
            'seller_id' => $sellerId,
            'title' => $request['title'],
            'description' => $request['description'] ?? null,
            'tags' => $this->parseTags($request['tags'] ?? null),
            'image' => $image,
            'image_storage_type' => $image ? $storage : 'public',
            'project_url' => $request['project_url'] ?? null,
            'completed_at' => $request['completed_at'] ?? null,
            'priority' => $priority,
            'is_active' => !$hasActiveItem,
        ];
    }

    public function getUpdateData(object $request, FreelancerPortfolioItem $item): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $image = $request->hasFile('image')
            ? $this->update(self::IMAGE_DIR, $item->image, 'webp', $request->file('image'))
            : $item->image;

        return [
            'title' => $request['title'],
            'description' => $request['description'] ?? null,
            'tags' => $this->parseTags($request['tags'] ?? null),
            'image' => $image,
            'image_storage_type' => $request->hasFile('image') ? $storage : $item->image_storage_type,
            'project_url' => $request['project_url'] ?? null,
            'completed_at' => $request['completed_at'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request['is_active'] : $item->is_active,
        ];
    }

    public function deleteImage(FreelancerPortfolioItem $item): bool
    {
        if (!empty($item->image)) {
            $this->delete(self::IMAGE_DIR . $item->image);
        }

        return true;
    }

    public function syncGalleryItems(FreelancerPortfolioItem $item, object $request): void
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $submittedIds = [];

        foreach ($request->input('gallery', []) as $index => $row) {
            $url = $row['url'] ?? null;
            $file = $request->file("gallery.$index.image");
            $existingId = $row['id'] ?? null;

            if ($existingId) {
                $galleryItem = FreelancerPortfolioGallery::where('id', $existingId)
                    ->where('freelancer_portfolio_item_id', $item->id)
                    ->first();

                if (!$galleryItem) {
                    continue;
                }

                $data = ['url' => $url, 'priority' => $index];
                if ($file) {
                    $data['image'] = $this->update(self::IMAGE_DIR, $galleryItem->image, 'webp', $file);
                    $data['image_storage_type'] = $storage;
                }
                $galleryItem->update($data);
                $submittedIds[] = $galleryItem->id;
            } elseif ($file) {
                $galleryItem = $item->galleryItems()->create([
                    'image' => $this->upload(self::IMAGE_DIR, 'webp', $file),
                    'image_storage_type' => $storage,
                    'url' => $url,
                    'priority' => $index,
                ]);
                $submittedIds[] = $galleryItem->id;
            }
        }

        $item->galleryItems()->whereNotIn('id', $submittedIds ?: [0])->get()->each(function (FreelancerPortfolioGallery $stale) {
            if (!empty($stale->image)) {
                $this->delete(self::IMAGE_DIR . $stale->image);
            }
            $stale->delete();
        });
    }

    public function deleteGalleryImages(FreelancerPortfolioItem $item): void
    {
        foreach ($item->galleryItems as $galleryItem) {
            if (!empty($galleryItem->image)) {
                $this->delete(self::IMAGE_DIR . $galleryItem->image);
            }
        }
    }

    private function parseTags(?string $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        return collect(explode(',', $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
