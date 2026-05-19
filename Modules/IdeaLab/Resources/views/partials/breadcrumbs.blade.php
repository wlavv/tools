<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('idealab.index') }}">Idea Lab</a></li>
        @isset($current)
            <li class="breadcrumb-item active" aria-current="page">{{ $current }}</li>
        @endisset
    </ol>
</nav>
