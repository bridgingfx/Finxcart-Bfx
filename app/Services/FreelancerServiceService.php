<?php

namespace App\Services;

use App\Models\FreelancerService;
use App\Models\FreelancerServiceImage;
use App\Models\FreelancerServicePackage;
use App\Traits\FileManagerTrait;

class FreelancerServiceService
{
    use FileManagerTrait;

    private const IMAGE_DIR = 'freelancer-services/';

    public const PACKAGE_TIERS = ['basic', 'standard', 'premium'];

    public function syncPackages(FreelancerService $service, object $request): void
    {
        $packages = $request->input('packages', []);

        foreach (self::PACKAGE_TIERS as $tier) {
            $tierData = $packages[$tier] ?? [];

            $features = [];
            foreach (($tierData['features'] ?? []) as $feature) {
                $label = trim($feature['label'] ?? '');
                if ($label === '') {
                    continue;
                }
                $features[] = [
                    'label' => $label,
                    'included' => !empty($feature['included']),
                ];
            }

            FreelancerServicePackage::updateOrCreate(
                ['freelancer_service_id' => $service->id, 'tier' => $tier],
                [
                    'is_enabled' => !empty($tierData['is_enabled']),
                    'title' => $tierData['title'] ?? null,
                    'description' => $tierData['description'] ?? null,
                    'price' => $tierData['price'] ?? null,
                    'delivery_time_days' => $tierData['delivery_time_days'] ?? null,
                    'revisions' => $tierData['revisions'] ?? null,
                    'features' => $features,
                ]
            );
        }
    }

    public function getCreateData(object $request, int $sellerId): array
    {
        return [
            'seller_id' => $sellerId,
            'freelancer_category_id' => $request['freelancer_category_id'],
            'freelancer_specialization_id' => $request['freelancer_specialization_id'],
            'title' => $request['title'],
            'description' => $request['description'] ?? null,
            'is_active' => true,
            'priority' => 0,
        ];
    }

    public function getUpdateData(object $request): array
    {
        return [
            'freelancer_category_id' => $request['freelancer_category_id'],
            'freelancer_specialization_id' => $request['freelancer_specialization_id'],
            'title' => $request['title'],
            'description' => $request['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request['is_active'] : true,
        ];
    }

    public function syncImages(FreelancerService $service, object $request): void
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $submittedIds = [];

        foreach ($request->input('images', []) as $index => $row) {
            $file = $request->file("images.$index.image");
            $existingId = $row['id'] ?? null;

            if ($existingId) {
                $image = FreelancerServiceImage::where('id', $existingId)
                    ->where('freelancer_service_id', $service->id)
                    ->first();

                if (!$image) {
                    continue;
                }

                $data = ['priority' => $index];
                if ($file) {
                    $data['image'] = $this->update(self::IMAGE_DIR, $image->image, 'webp', $file);
                    $data['image_storage_type'] = $storage;
                }
                $image->update($data);
                $submittedIds[] = $image->id;
            } elseif ($file) {
                $image = $service->images()->create([
                    'image' => $this->upload(self::IMAGE_DIR, 'webp', $file),
                    'image_storage_type' => $storage,
                    'priority' => $index,
                ]);
                $submittedIds[] = $image->id;
            }
        }

        $service->images()->whereNotIn('id', $submittedIds ?: [0])->get()->each(function (FreelancerServiceImage $stale) {
            if (!empty($stale->image)) {
                $this->delete(self::IMAGE_DIR . $stale->image);
            }
            $stale->delete();
        });
    }

    public function deleteImages(FreelancerService $service): void
    {
        foreach ($service->images as $image) {
            if (!empty($image->image)) {
                $this->delete(self::IMAGE_DIR . $image->image);
            }
        }
    }
}
