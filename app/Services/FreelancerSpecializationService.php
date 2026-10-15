<?php

namespace App\Services;

use App\Models\FreelancerSpecialization;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class FreelancerSpecializationService
{
    use FileManagerTrait;

    private const DIRECTORY = 'freelancer-specialization/';
    private const DEFAULT_IMAGE = 'def.png';
    private const MAX_PRIORITY = 20;

    public function getAddData(object $request, int $rowIndex = 0): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $defaultName = $this->getDefaultName(request: $request, rowIndex: $rowIndex);

        return [
            'freelancer_category_id' => $request['freelancer_category_id'],
            'name' => $defaultName,
            'slug' => $this->getUniqueSlug(name: $defaultName),
            'description' => $this->getDefaultDescription(request: $request, rowIndex: $rowIndex),
            'image' => $this->upload(dir: self::DIRECTORY, format: 'webp', image: $request->file('image')),
            'image_storage_type' => $request->hasFile('image') ? $storage : 'public',
            'priority' => $this->getValidatedPriority(priority: $request['priority']),
            'is_active' => (int)$request->get('is_active', 1),
        ];
    }

    public function getTranslationNamesForRow(object $request, int $rowIndex): array
    {
        $names = [];

        foreach (($request['lang'] ?? []) as $languageIndex => $language) {
            $names[$languageIndex] = $request['name'][$languageIndex][$rowIndex] ?? null;
        }

        return $names;
    }

    public function getTranslationDescriptionsForRow(object $request, int $rowIndex): array
    {
        $descriptions = [];

        foreach (($request['lang'] ?? []) as $languageIndex => $language) {
            $descriptions[$languageIndex] = $request['description'][$languageIndex][$rowIndex] ?? null;
        }

        return $descriptions;
    }

    public function getUpdateData(object $request, object $data): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $defaultName = $this->getDefaultName(request: $request);
        $image = $request->hasFile('image')
            ? $this->update(dir: self::DIRECTORY, oldImage: $data['image'] ?? null, format: 'webp', image: $request->file('image'))
            : ($data['image'] ?? self::DEFAULT_IMAGE);

        return [
            'freelancer_category_id' => $request['freelancer_category_id'],
            'name' => $defaultName,
            'slug' => $this->getUniqueSlug(name: $defaultName, ignoredId: $data['id']),
            'description' => $this->getDefaultDescription(request: $request),
            'image' => $image,
            'image_storage_type' => $request->hasFile('image') ? $storage : ($data['image_storage_type'] ?? 'public'),
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

    private function getDefaultName(object $request, int $rowIndex = 0): string
    {
        $defaultLanguage = getDefaultLanguage();
        $defaultIndex = array_search($defaultLanguage, $request['lang']);

        if ($defaultIndex === false) {
            $defaultIndex = 0;
        }

        $defaultName = $request['name'][$defaultIndex] ?? '';

        if (is_array($defaultName)) {
            return $defaultName[$rowIndex];
        }

        return $defaultName;
    }

    private function getDefaultDescription(object $request, int $rowIndex = 0): ?string
    {
        $defaultLanguage = getDefaultLanguage();
        $defaultIndex = array_search($defaultLanguage, $request['lang'] ?? []);

        if ($defaultIndex === false) {
            $defaultIndex = 0;
        }

        $description = $request['description'][$defaultIndex] ?? null;

        if (is_array($description)) {
            return $description[$rowIndex] ?? null;
        }

        return $description;
    }

    private function getUniqueSlug(string $name, int|string|null $ignoredId = null): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug ?: Str::random(8);
        $index = 1;

        while (
            FreelancerSpecialization::withoutGlobalScope('translate')
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
