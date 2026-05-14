<div class="dbx-toolbar">
    <form method="GET" action="{{ route('database_explorer.index') }}" class="dbx-toolbar-form">
        <div class="dbx-form-row">
            <label class="dbx-label">Schema</label>
            <select name="schema" class="dbx-select">
                <option value="">All allowed schemas</option>
                @foreach($schemas as $schema)
                    <option value="{{ $schema['schemaName'] }}" {{ ($filters['schemaName'] ?? null) === $schema['schemaName'] ? 'selected' : '' }}>{{ $schema['schemaName'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="dbx-form-row">
            <label class="dbx-label">Table search</label>
            <input type="text" name="q" value="{{ $filters['search'] ?? '' }}" class="dbx-input" placeholder="orders, users, audit...">
        </div>
        <div class="dbx-form-row">
            <label class="dbx-label">Health</label>
            <select name="health" class="dbx-select">
                <option value="">Any status</option>
                @foreach(['healthy', 'warning', 'degraded', 'critical'] as $status)
                    <option value="{{ $status }}" {{ ($filters['health'] ?? null) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-filter"></i> Filter</button>
        <a href="{{ route('database_explorer.index') }}" class="lsg-action-btn lsg-action-btn--compact"><i class="fa-solid fa-rotate-left"></i> Reset</a>
    </form>
</div>
