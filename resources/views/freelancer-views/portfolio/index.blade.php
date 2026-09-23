@extends('layouts.freelancer.app')

@section('title', translate('manage_portfolio'))

@push('css_or_js')
    <style>
        .portfolio-table-card { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; overflow: hidden; }
        .portfolio-table { margin-bottom: 0; }
        .portfolio-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e8edf3; padding: 14px 16px; white-space: nowrap;
        }
        .portfolio-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .portfolio-table tbody tr:last-child td { border-bottom: none; }
        .portfolio-table tbody tr:hover { background: #f9fafc; }

        .portfolio-row-item { display: flex; align-items: center; gap: 12px; }
        .portfolio-row-thumb { width: 56px; height: 42px; border-radius: 8px; object-fit: cover; background: #f1f4f8; flex-shrink: 0; }
        .portfolio-row-thumb-placeholder {
            width: 56px; height: 42px; border-radius: 8px; background: #f6f8fb; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; color: #c3ccd6; font-size: 16px;
        }
        .portfolio-row-title { font-weight: 700; color: #1f2937; }
        .portfolio-row-description {
            color: #9ca3af; font-size: 12px; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .portfolio-tag-chip {
            display: inline-block; background: #eef3f8; color: #1c2b3a; border-radius: 999px;
            padding: 2px 10px; font-size: 11px; font-weight: 600; margin: 0 4px 4px 0;
        }

        .portfolio-empty {
            background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 48px; text-align: center; color: #6b7280;
        }
        .portfolio-empty i { font-size: 34px; color: #d8dee7; display: block; margin-bottom: 10px; }

        @media (max-width: 767.98px) {
            .portfolio-table thead { display: none; }
            .portfolio-table, .portfolio-table tbody, .portfolio-table tr, .portfolio-table td { display: block; width: 100%; }
            .portfolio-table tr { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .portfolio-table td { border-bottom: none !important; padding: 6px 16px; }
            .portfolio-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('manage_portfolio') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $portfolioItems->count() }}</span>
            </h2>
            <a href="{{ route('freelancer.portfolio.create') }}" id="add-portfolio-item-btn"
               data-approved="{{ auth('freelancer')->user()?->status === 'approved' ? '1' : '0' }}"
               class="btn btn--primary text-nowrap">
                <i class="tio-add"></i>
                {{ translate('add_portfolio_item') }}
            </a>
        </div>

        @if($portfolioItems->count() == 0)
            <div class="portfolio-empty">
                <i class="tio-photo-gallery-outlined"></i>
                <p class="mb-0">{{ translate('no_portfolio_item_found') }}</p>
            </div>
        @else
            <div class="portfolio-table-card">
                <div class="table-responsive">
                    <table class="table portfolio-table">
                        <thead>
                            <tr>
                                <th>{{ translate('item') }}</th>
                                <th>{{ translate('skills') }} / {{ translate('tags') }}</th>
                                <th>{{ translate('completed_at') }}</th>
                                <th>{{ translate('status') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($portfolioItems as $item)
                                <tr>
                                    <td data-label="{{ translate('item') }}">
                                        <div class="portfolio-row-item">
                                            @if($item->image_full_url)
                                                <img class="portfolio-row-thumb" alt=""
                                                     src="{{ getStorageImages($item->image_full_url, type: 'backend-profile') }}">
                                            @else
                                                <span class="portfolio-row-thumb-placeholder"><i class="tio-photo-gallery-outlined"></i></span>
                                            @endif
                                            <div>
                                                <div class="portfolio-row-title">{{ $item->title }}</div>
                                                <div class="portfolio-row-description" title="{{ $item->description }}">
                                                    {{ $item->description ?: translate('no_description_added') }}
                                                </div>
                                            </div>
                                            @if($item->galleryItems->count())
                                                <span class="badge badge-soft-dark text-nowrap">
                                                    <i class="tio-photo-camera"></i> +{{ $item->galleryItems->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="{{ translate('skills') }}">
                                        @if(!empty($item->tags))
                                            @foreach(array_slice($item->tags, 0, 3) as $tag)
                                                <span class="portfolio-tag-chip">{{ $tag }}</span>
                                            @endforeach
                                            @if(count($item->tags) > 3)
                                                <span class="portfolio-tag-chip">+{{ count($item->tags) - 3 }}</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="{{ translate('completed_at') }}">
                                        {{ $item->completed_at?->format('d M, Y') ?? '-' }}
                                    </td>
                                    <td data-label="{{ translate('status') }}">
                                        @if($item->is_active)
                                            <span class="badge badge-soft-success">{{ translate('active') }}</span>
                                        @else
                                            <form action="{{ route('freelancer.portfolio.activate', [$item->id]) }}" method="post" class="d-inline" novalidate>
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap">
                                                    {{ translate('set_active') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td data-label="{{ translate('action') }}">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($item->project_url)
                                                <a href="{{ $item->project_url }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm square-btn" title="{{ translate('project_url') }}">
                                                    <i class="tio-open-in-new"></i>
                                                </a>
                                            @endif
                                            <a class="btn btn-outline-info btn-sm square-btn"
                                               href="{{ route('freelancer.portfolio.view', [$item->id]) }}"
                                               title="{{ translate('view') }}">
                                                <i class="tio-eye"></i>
                                            </a>
                                            <a class="btn btn-outline--primary btn-sm square-btn"
                                               href="{{ route('freelancer.portfolio.edit', [$item->id]) }}"
                                               title="{{ translate('edit') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-outline-danger btn-sm square-btn delete-data"
                                               data-id="freelancer-portfolio-{{ $item->id }}"
                                               title="{{ translate('delete') }}"
                                               href="javascript:">
                                                <i class="tio-delete"></i>
                                            </a>
                                        </div>
                                        <form action="{{ route('freelancer.portfolio.destroy', [$item->id]) }}"
                                              method="post" id="freelancer-portfolio-{{ $item->id }}" novalidate>
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('script')
    <script>
        document.getElementById('add-portfolio-item-btn')?.addEventListener('click', function (e) {
            if (this.dataset.approved !== '1') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '{{ translate('Not_Approved_Yet') }}',
                    text: '{{ translate('your_documents_must_be_approved_by_admin_before_adding_portfolio_items') }}',
                    confirmButtonText: '{{ translate('OK') }}',
                });
            }
        });

        @if(session('freelancer_kyc_popup_message'))
            Swal.fire({
                icon: 'warning',
                title: '{{ translate('Not_Approved_Yet') }}',
                text: '{{ session('freelancer_kyc_popup_message') }}',
                confirmButtonText: '{{ translate('OK') }}',
            });
        @endif
    </script>
@endpush
