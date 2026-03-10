<div class="pm-card pm-table-wrap">
    <table class="pm-table">
        <thead>
            <tr>
                <th>Projeto</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Deadline</th>
                <th>Principal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->status }}</td>
                    <td>{{ $project->progress_percent }}%</td>
                    <td>{{ optional($project->deadline)->format('Y-m-d') }}</td>
                    <td>{{ $project->contact_name }}</td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('project_manager.show', $project) }}">Abrir</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Sem projetos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
