<div class="table-responsive">
    <table class="table table-sm table-striped lsg-datatable">
        <thead>
            <tr>
                <th>Validator</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Code</th>
                <th>Finding</th>
                <th>File</th>
            </tr>
        </thead>
        <tbody>
            @foreach($findings as $finding)
                <tr>
                    <td>{{ $finding->validator_key }}</td>
                    <td>@include('module-compliance-center::partials.status-badge', ['status' => $finding->severity])</td>
                    <td>@include('module-compliance-center::partials.status-badge', ['status' => $finding->status])</td>
                    <td><code>{{ $finding->code }}</code></td>
                    <td>
                        <strong>{{ $finding->title }}</strong>
                        @if($finding->message)<div class="small text-muted">{{ $finding->message }}</div>@endif
                        @if($finding->recommendation)<div class="small text-warning">{{ $finding->recommendation }}</div>@endif
                    </td>
                    <td class="small">{{ $finding->file_path }}@if($finding->line_number):{{ $finding->line_number }}@endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
