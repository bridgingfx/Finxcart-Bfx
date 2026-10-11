{{--
    _fx-pagination.blade.php — fx-styled pagination for a LengthAwarePaginator.
    Presentation only: uses paginator URLs (query strings preserved by caller via appends()).
    Expects: $paginator (Illuminate\Pagination\LengthAwarePaginator)
--}}
@if($paginator->hasPages())
    <nav aria-label="{{ translate('pagination') }}">
        <ul class="fx-pagination">
            @if($paginator->onFirstPage())
                <li><span aria-disabled="true" aria-label="{{ translate('previous_page') }}">&lsaquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ translate('previous_page') }}">&lsaquo;</a></li>
            @endif

            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if($page == $paginator->currentPage())
                    <li><span aria-current="page">{{ $page }}</span></li>
                @else
                    <li><a href="{{ $url }}" aria-label="{{ translate('go_to_page') }} {{ $page }}">{{ $page }}</a></li>
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ translate('next_page') }}">&rsaquo;</a></li>
            @else
                <li><span aria-disabled="true" aria-label="{{ translate('next_page') }}">&rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
