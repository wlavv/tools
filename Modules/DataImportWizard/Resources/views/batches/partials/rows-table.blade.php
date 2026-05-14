<div class="card">
    <h2>Linhas</h2>

    <table>
        <thead>
        <tr>
            <th>Linha</th>
            <th>Estado</th>
            <th>Operação</th>
            <th>Target</th>
            <th>Erros</th>
            <th>Avisos</th>
            <th>Dados</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row->row_number }}</td>
                <td>
                    @if (in_array($row->status, ['valid', 'imported', 'completed']))
                        <span class="badge ok">{{ $row->status }}</span>
                    @elseif (in_array($row->status, ['invalid', 'failed']))
                        <span class="badge err">{{ $row->status }}</span>
                    @else
                        <span class="badge">{{ $row->status }}</span>
                    @endif
                </td>
                <td>{{ $row->operation ?: '-' }}</td>
                <td>
                    @if ($row->target_model)
                        <code>{{ class_basename($row->target_model) }} #{{ $row->target_id }}</code>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if (!empty($row->errors))
                        <ul>
                            @foreach ($row->errors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if (!empty($row->warnings))
                        <ul>
                            @foreach ($row->warnings as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    @else
                        -
                    @endif
                </td>
                <td><pre>{{ json_encode($row->normalized_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $rows->links() }}
</div>
