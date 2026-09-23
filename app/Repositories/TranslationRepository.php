<?php

namespace App\Repositories;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Translation;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function __construct(
       private readonly Translation $translation
    )
    {
    }

    public function add(object $request, string $model, int|string $id): bool
    {
        $rows = [];
        foreach ($request->lang as $index => $key) {
            foreach (['name','description','title'] as $type){
                if (isset($request[$type][$index]) && $key != 'en') {
                    $rows[] = [
                        'translationable_type' => $model,
                        'translationable_id' => $id,
                        'locale' => $key,
                        'key' => $type,
                        'value' => $request[$type][$index]
                    ];
                }
            }
        }
        if (!empty($rows)) {
            $this->translation->insert($rows);
        }
        return true;
    }

    public function update(object $request, string $model, int|string $id): bool
    {
        $locales = [];
        foreach ($request->lang as $index => $key) {
            if ($key != 'en') {
                $locales[] = $key;
            }
        }

        if (empty($locales)) {
            return true;
        }

        $existing = $this->translation
            ->where('translationable_type', $model)
            ->where('translationable_id', $id)
            ->whereIn('locale', $locales)
            ->whereIn('key', ['name', 'description', 'title'])
            ->get()
            ->keyBy(fn ($row) => $row->locale . '|' . $row->key);

        $insertRows = [];
        foreach ($request->lang as $index => $key) {
            foreach (['name','description','title'] as $type){
                if (isset($request[$type][$index]) && $key != 'en') {
                    $existingRow = $existing->get($key . '|' . $type);
                    if ($existingRow) {
                        $existingRow->value = $request[$type][$index];
                        $existingRow->save();
                    } else {
                        $insertRows[] = [
                            'translationable_type' => $model,
                            'translationable_id' => $id,
                            'locale' => $key,
                            'key' => $type,
                            'value' => $request[$type][$index]
                        ];
                    }
                }
            }
        }

        if (!empty($insertRows)) {
            $this->translation->insert($insertRows);
        }

        return true;
    }
    public function updateData(string $model, string $id, string $lang, string $key, string $value):bool
    {
        $this->translation->updateOrInsert(
            [
                'translationable_type' => $model,
                'translationable_id' => $id,
                'locale' => $lang,
                'key' => $key
            ],
            [
                'value' => $value
            ]
        );
        return true;
    }
    public function delete(string $model, int|string $id): bool
    {
        $this->translation->where('translationable_type',$model)->where('translationable_id',$id)->delete();
        return true;
    }
}
