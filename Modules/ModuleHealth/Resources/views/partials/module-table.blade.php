<div class="table-responsive mh-table-wrap">
    <table class="table mh-table align-middle">
        <thead>
            <tr>
                <th>Module</th>
                <th>Profile</th>
                <th>Status</th>
                <th>Completion</th>
                <th>Required</th>
                <th>Recommended</th>
                <th>Optional</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        <div class="mh-module-name">{{ $item->module_name }}</div>
                        <div class="mh-muted small">{{ $item->module_slug }}</div>
                    </td>
                    <td><span class="mh-pill">{{ $item->profile }}</span></td>
                    <td>@include('module-health::partials.status', ['status' => $item->status])</td>
                    <td>
                        <div class="mh-progress-wrap">
                            <div class="mh-progress"><span style="width: {{ $item->completion }}%"></span></div>
                            <span class="mh-progress-value">{{ $item->completion }}%</span>
                        </div>
                    </td>
                    <td>{{ $item->required_found }}/{{ $item->required_total }}</td>
                    <td>{{ $item->recommended_found }}/{{ $item->recommended_total }}</td>
                    <td>{{ $item->optional_found }}/{{ $item->optional_total }}</td>
                    <td class="text-end">
                        <a href="{{ route('module_health.modules.show', $item) }}" class="btn btn-sm btn-outline-primary mh-icon-btn" title="View module">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="mh-empty">No scan data available.</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
