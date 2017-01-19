@if ($paginator->hasPages())
    <ul class="pagination">
        <li class="disabled">
            <span>共{{ $paginator->total() }}条</span>
            <span>每页{{ $paginator->count() }}条</span>
        </li>
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled"><span>首页</span></li>
            <li class="disabled"><span>&laquo;</span></li>
        @else
            <li><a href="{{ $paginator->url(1) }}" rel="prev">首页</a></li>
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
            <li><a href="{{ $paginator->url( $paginator->lastPage() ) }}" rel="next">尾页</a></li>
        @else
            <li class="disabled"><span>&raquo;</span></li>
            <li class="disabled"><span>尾页</span></li>
        @endif
    </ul>
@endif
