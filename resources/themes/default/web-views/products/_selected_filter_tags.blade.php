@if (
    $tags_category != null ||
    $tags_brands != null ||
    $selectedRatings != null ||
    (isset($sort_by) && $sort_by != null && $sort_by != 'latest') ||
    isset($publishingHouse) && count($publishingHouse) > 0 ||
    isset($productAuthors) && count($productAuthors) > 0
    )
    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
        <span class="text-nowrap fs-13 text-muted">{{ translate('Applied_Filters') }}:</span>

        @isset($sort_by)
            @if ($sort_by != 'latest')
                <span class="badge bg-white border text-primary rounded-16px px-3 py-1 fs-12 fw-semibold remove_tags_sortBy">
                    {{ ucwords(translate($sort_by)) }}
                    <button type="button" class="btn btn-link text-primary p-0 m-0 border-0"><i class="tio-clear"></i></button>
                </span>
            @endif
        @endisset

        @isset($tags_category)
            @foreach ($tags_category as $item)
                <span class="badge bg-white border text-primary rounded-16px px-3 py-1 fs-12 fw-semibold remove_tags_Category" data-id="{{ $item->id }}">
                    {{ Str::limit($item->name, 20, '...') }}
                    <button type="button" class="btn btn-link text-primary p-0 m-0 border-0"><i class="tio-clear"></i></button>
                </span>
            @endforeach
        @endisset

        @isset($tags_brands)
            @foreach ($tags_brands as $item)
                <span class="badge bg-white border text-primary rounded-16px px-3 py-1 fs-12 fw-semibold remove_tags_Brand" data-id="{{ $item->id }}">
                    {{ Str::limit($item->name, 20, '...') }}
                    <button type="button" class="btn btn-link text-primary p-0 m-0 border-0"><i class="tio-clear"></i></button>
                </span>
            @endforeach
        @endisset

        @isset($publishingHouse)
            @foreach ($publishingHouse as $item)
                <span class="badge bg-white border text-primary rounded-16px px-3 py-1 fs-12 fw-semibold remove_tags_publishing_house" data-id="{{ $item->id }}">
                    {{ Str::limit($item->name, 20, '...') }}
                    <button type="button" class="btn btn-link text-primary p-0 m-0 border-0"><i class="tio-clear"></i></button>
                </span>
            @endforeach
        @endisset

        @isset($productAuthors)
            @foreach ($productAuthors as $item)
                <span class="badge bg-white border text-primary rounded-16px px-3 py-1 fs-12 fw-semibold remove_tags_author_id" data-id="{{ $item->id }}">
                    {{ Str::limit($item->name, 20, '...') }}
                    <button type="button" class="btn btn-link text-primary p-0 m-0 border-0"><i class="tio-clear"></i></button>
                </span>
            @endforeach
        @endisset

        @isset($selectedRatings)
            @foreach ($selectedRatings as $item)
                <span class="badge bg-white border text-primary rounded-16px px-3 py-1 fs-12 fw-semibold remove_tags_review" data-id="{{ $item }}">
                    ★ {{ $item }}
                    <button type="button" class="btn btn-link text-primary p-0 m-0 border-0"><i class="tio-clear"></i></button>
                </span>
            @endforeach
        @endisset
    </div>
@endif
