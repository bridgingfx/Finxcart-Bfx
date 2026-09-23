<?php

namespace App\Services;

use App\Models\FreelancerCategory;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class FreelancerCategoryService
{
    use FileManagerTrait;

    private const DIRECTORY = 'freelancer-category/';
    private const DEFAULT_IMAGE = 'def.png';
    private const MAX_PRIORITY = 20;

    public function getAddData(object $request): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $defaultName = $this->getDefaultName(request: $request);

        return [
            'name' => $defaultName,
            'slug' => $this->getUniqueSlug(name: $defaultName),
            'image' => $this->upload(dir: self::DIRECTORY, format: 'webp', image: $request->file('image')),
            'image_storage_type' => $request->hasFile('image') ? $storage : 'public',
            'parent_id' => null,
            'position' => 0,
            'priority' => $this->getValidatedPriority(priority: $request['priority']),
            'is_active' => (int)$request->get('is_active', 1),
        ];
    }

    public function getUpdateData(object $request, object $data): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $defaultName = $this->getDefaultName(request: $request);
        $image = $request->hasFile('image')
            ? $this->update(dir: self::DIRECTORY, oldImage: $data['image'] ?? null, format: 'webp', image: $request->file('image'))
            : ($data['image'] ?? self::DEFAULT_IMAGE);

        return [
            'name' => $defaultName,
            'slug' => $this->getUniqueSlug(name: $defaultName, ignoredId: $data['id']),
            'image' => $image,
            'image_storage_type' => $request->hasFile('image') ? $storage : ($data['image_storage_type'] ?? 'public'),
            'parent_id' => null,
            'position' => 0,
            'priority' => $this->getValidatedPriority(priority: $request['priority']),
            'is_active' => (int)$request->get('is_active', $data['is_active'] ?? 1),
        ];
    }

    public function deleteImage(?object $data): bool
    {
        if (!$data || empty($data['image']) || $data['image'] === self::DEFAULT_IMAGE) {
            return true;
        }

        $this->delete(path: self::DIRECTORY . $data['image']);
        return true;
    }

    private function getDefaultName(object $request): string
    {
        $defaultLanguage = getDefaultLanguage();
        $defaultIndex = array_search($defaultLanguage, $request['lang']);

        if ($defaultIndex === false) {
            $defaultIndex = 0;
        }

        return $request['name'][$defaultIndex];
    }

    private function getUniqueSlug(string $name, int|string|null $ignoredId = null): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug ?: Str::random(8);
        $index = 1;

        while (
            FreelancerCategory::withoutGlobalScope('translate')
                ->where('slug', $slug)
                ->when($ignoredId, fn ($query) => $query->where('id', '!=', $ignoredId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $index++;
        }

        return $slug;
    }

    private function getValidatedPriority(int|string $priority): int
    {
        return min((int)$priority, self::MAX_PRIORITY);
    }
}
