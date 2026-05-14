@extends('module-health::layouts.module')

@section('content')
@include('module-health::partials.styles')

<div class="mh-shell">
    <div class="mh-detail-grid">
        <div class="mh-card mh-panel">
            <h5 class="mh-title">{{ $item->module_name }}</h5>
            <div class="mh-subtitle mh-path">{{ $item->module_path }}</div>

            <div class="mt-3">
                @include('module-health::partials.status', ['status' => $item->status])
            </div>

            <div class="mt-4">
                <div class="d-flex justify-content-between small mh-muted mb-2">
                    <span>Completion</span>
                    <span>{{ $item->completion }}%</span>
                </div>
                <div class="mh-progress"><span style="width: {{ $item->completion }}%"></span></div>
            </div>

            <hr>

            <div class="mh-mini-stats">
                <div class="mh-mini-stat">
                    <span>Required</span>
                    <strong>{{ $item->required_found }}/{{ $item->required_total }}</strong>
                </div>
                <div class="mh-mini-stat">
                    <span>Recommended</span>
                    <strong>{{ $item->recommended_found }}/{{ $item->recommended_total }}</strong>
                </div>
                <div class="mh-mini-stat">
                    <span>Optional</span>
                    <strong>{{ $item->optional_found }}/{{ $item->optional_total }}</strong>
                </div>
            </div>
        </div>

        <div class="mh-shell">
            <div class="mh-card mh-panel">
                <div class="mh-card-head">
                    <div>
                        <h6 class="mh-title">Detected Components</h6>
                        <div class="mh-subtitle">Files and conventions found for this module.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mh-table">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Component</th>
                                <th>Status</th>
                                <th>Matches</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($item->components ?? []) as $component)
                                <tr>
                                    <td><span class="mh-pill">{{ $component['group'] }}</span></td>
                                    <td class="mh-module-name">{{ $component['label'] }}</td>
                                    <td>
                                        @if($component['present'])
                                            <span class="mh-badge mh-enhanced">Found</span>
                                        @else
                                            <span class="mh-badge mh-broken">Missing</span>
                                        @endif
                                    </td>
                                    <td class="small mh-muted mh-path">{{ implode(', ', $component['matches'] ?? []) ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="mh-empty">No components were recorded for this module.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mh-card mh-panel">
                <h6 class="mh-title mb-3">Recommendations</h6>
                @forelse(($item->recommendations ?? []) as $recommendation)
                    <div class="mh-recommendation">
                        <span class="mh-badge mh-incomplete mh-rec-type">{{ strtoupper($recommendation['type']) }}</span>
                        <div>{{ $recommendation['label'] }}</div>
                    </div>
                @empty
                    <div class="mh-empty">No recommendations for this module.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
