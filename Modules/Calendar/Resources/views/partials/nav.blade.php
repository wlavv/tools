<nav class="calendar-module-nav" aria-label="Calendar navigation">
    <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.index') ? 'is-active' : '' }}">
        <i class="fa-solid fa-calendar-days"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('calendar.events.index') }}" class="{{ request()->routeIs('calendar.events.*') ? 'is-active' : '' }}">
        <i class="fa-solid fa-calendar-check"></i><span>Events</span>
    </a>
    <a href="{{ route('calendar.contexts.index') }}" class="{{ request()->routeIs('calendar.contexts.*') ? 'is-active' : '' }}">
        <i class="fa-solid fa-layer-group"></i><span>Contexts</span>
    </a>
    <a href="{{ route('calendar.categories.index') }}" class="{{ request()->routeIs('calendar.categories.*') ? 'is-active' : '' }}">
        <i class="fa-solid fa-tags"></i><span>Categories</span>
    </a>
    <a href="{{ route('calendar.tablet', ['context' => request('context') ?: 'family']) }}" class="{{ request()->routeIs('calendar.tablet') ? 'is-active' : '' }}">
        <i class="fa-solid fa-tablet-screen-button"></i><span>Tablet</span>
    </a>
</nav>
