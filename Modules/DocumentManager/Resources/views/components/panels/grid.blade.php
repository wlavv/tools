<div class="dms-grid dms-grid--4">
    @foreach($panels as $panel)
        <div class="dms-panel dms-panel--{{ $panel['tone'] ?? 'primary' }}">
            <div class="dms-panel__icon"><i class="{{ $panel['icon'] }}"></i></div>
            <div>
                <span>{{ $panel['label'] }}</span>
                <strong>{{ $panel['count'] }}</strong>
            </div>
        </div>
    @endforeach
</div>
