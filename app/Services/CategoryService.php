<?php

namespace App\Services;

use App\Models\Category;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    use FileManagerTrait;

    public function __construct(
        private readonly Category $category,
    ) {
    }

    private const MAX_PRIORITY = 20;

    public function getAddData(object $request): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $parentId = (int)$request->get('parent_id', 0);
        $parentCategory = $parentId ? $this->category->find($parentId) : null;
        $directory = $this->getImageDirectoryByPosition(position: (int)$request['position']);

        return [
            'name' => $request['name'][array_search('en', $request['lang'])],
            'slug' => Str::slug($request['name'][array_search('en', $request['lang'])]),
            'icon' => $this->upload($directory, 'webp', $request->file('image')),
            'icon_storage_type' => $request->has('image') ? $storage : null,
            'parent_id' => $parentId,
            'position' => $request['position'],
            'priority' => $this->getValidatedPriority(priority: $request['priority']),
            'home_status' => $parentCategory ? (int)$parentCategory->home_status : (int)$request->get('home_status', 1),
        ];
    }

    public function getUpdateData(object $request, object $data): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $directory = $this->getImageDirectoryByPosition(position: (int)$data['position']);
        $image = $request->file('image')
            ? $this->update($directory, $data['icon'] ?? null, 'webp', $request->file('image'))
            : ($data['icon'] ?? null);

        return [
            'name' => $request['name'][array_search('en', $request['lang'])],
            'slug' => Str::slug($request['name'][array_search('en', $request['lang'])]),
            'icon' => $image,
            'icon_storage_type' => $request->has('image') ? $storage : ($data['icon_storage_type'] ?? null),
            'priority' => $this->getValidatedPriority(priority: $request['priority']),
        ];
    }

    public function getSelectOptionHtml(object $data): string
    {
        $output = '<option value="" disabled selected>' . (translate('select_sub_category')) . '</option>';
        foreach ($data as $row) {
            $output .= '<option value="' . $row->id . '">' . $row->defaultName . '</option>';
        }
        return $output;
    }

    public function deleteImages(object $data): bool
    {
        foreach ($this->collectCategoryTree(data: $data) as $category) {
            if (!empty($category['icon'])) {
                $directory = $this->getImageDirectoryByPosition(position: (int)$category['position']);
                $this->delete($directory . $category['icon']);

                if ((int)$category['position'] !== 0) {
                    $this->delete('category/' . $category['icon']);
                }
            }
        }

        return true;
    }

    public function deleteCategoryTree(?object $category): bool
    {
        if (!$category) {
            return false;
        }

        $this->deleteImages(data: $category);
        $category->delete();

        return true;
    }

    public function syncCategoryTreeHomeStatus(object $category, int $homeStatus): bool
    {
        $categoryIds = $this->collectCategoryTree(data: $category)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int)$id)
            ->values()
            ->all();

        if (empty($categoryIds)) {
            return false;
        }

        $this->category->whereIn('id', $categoryIds)->update([
            'home_status' => $homeStatus,
        ]);
        cacheRemoveByType(type: 'categories');

        return true;
    }

    private function collectCategoryTree(object $data): Collection
    {
        $categories = collect([$data]);

        foreach (($data->childes ?? []) as $child) {
            $categories = $categories->merge($this->collectCategoryTree(data: $child));
        }

        return $categories;
    }

    private function getValidatedPriority(int|string $priority): int
    {
        return min((int)$priority, self::MAX_PRIORITY);
    }

    private function getImageDirectoryByPosition(int $position): string
    {
        return match ($position) {
            1 => 'sub-categories/',
            2 => 'sub-sub-categories/',
            default => 'category/',
        };
    }
}
