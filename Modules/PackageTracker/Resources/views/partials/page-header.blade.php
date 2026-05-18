<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('package_tracker.dashboard') }}">Package Tracker</a></li>
                @isset($breadcrumb)<li class="breadcrumb-item active">{{ $breadcrumb }}</li>@endisset
            </ol>
        </nav>
        <h1 class="h3 mb-0">{{ $title ?? 'Package Tracker' }}</h1>
        @isset($subtitle)<p class="text-muted mb-0">{{ $subtitle }}</p>@endisset
    </div>
    <div class="d-flex gap-2">
        {{ $actions ?? '' }}
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $errors->first() }}</div>
@endif
