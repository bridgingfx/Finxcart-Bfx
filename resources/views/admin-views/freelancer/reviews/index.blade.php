@extends('layouts.admin.app')

@section('title', translate('freelancer_reviews'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_reviews') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $reviews->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('type') }}</th>
                        <th>{{ translate('reference') }}</th>
                        <th>{{ translate('reviewer') }}</th>
                        <th>{{ translate('rating') }}</th>
                        <th>{{ translate('review') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($reviews as $key => $review)
                        <tr>
                            <td>{{ $reviews->firstItem() + $key }}</td>
                            <td>
                                <span class="badge badge-soft-{{ $review->source === 'contract' ? 'success' : 'secondary' }} text-capitalize">
                                    {{ $review->source === 'contract' ? translate('verified_contract') : translate($review->source) }}
                                </span>
                            </td>
                            <td>
                                @if($review->link)
                                    <a href="{{ $review->link }}">{{ $review->link_label }}</a>
                                @else
                                    {{ $review->link_label }}
                                @endif
                            </td>
                            <td>{{ $review->reviewer_name }}</td>
                            <td class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fi {{ $i > $review->rating ? 'fi-rr-star' : 'fi-sr-star' }}"></i>
                                @endfor
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($review->body, 80) }}</td>
                            <td class="text-center">
                                <form action="{{ $review->delete_route }}" method="post"
                                      onsubmit="return confirm('{{ translate('are_you_sure') }}?')" novalidate>
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fi fi-rr-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>
@endsection
