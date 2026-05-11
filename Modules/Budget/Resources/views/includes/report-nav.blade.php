<div class="col-12 mb-3">
    <div class="card shadow-sm border-0 budget-report-nav">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h4 class="mb-1">Budget Reports</h4>
                <div class="text-muted small">Analysis by category, subcategory and annual evolution.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach(($actions ?? []) as $action)
                    <a href="{{ $action['url'] }}" class="{!! $action['class'] !!}">{!! $action['icon'] !!} <span class="ms-1">{{ $action['name'] }}</span></a>
                @endforeach
            </div>
        </div>
    </div>
</div>

