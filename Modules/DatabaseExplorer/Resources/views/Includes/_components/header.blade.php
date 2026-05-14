<div class="dbx-page-header">
    <div class="dbx-page-header__main">
        <div class="dbx-page-header__identity">
            <span class="dbx-page-header__icon"><i class="{{ config('database-explorer.ui.icon', 'fa-solid fa-database') }}"></i></span>
            <div>
                <h1 class="dbx-page-header__title">{{ $title ?? config('database-explorer.ui.module_name', 'Database Explorer') }}</h1>
                <p class="dbx-page-header__subtitle">{{ $subtitle ?? 'Analyze database metadata, table structure and technical health without browsing business data.' }}</p>
            </div>
        </div>
        <div class="dbx-page-actions">
            <a href="{{ route('database_explorer.index') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-table-list"></i> Tables</a>
            <a href="{{ route('database_explorer.health') }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-heart-pulse"></i> Health</a>
            <a href="{{ route('database_explorer.snapshots') }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"><i class="fa-solid fa-clock-rotate-left"></i> Snapshots</a>
        </div>
    </div>
</div>
