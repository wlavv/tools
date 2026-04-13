@extends('tasks::layouts.tablet')

@section('content')
@php
    $memberParam = $selectedMember?->slug ?? request('member');
    $tabletKey = $tabletKey ?? request('key');

    $buildPlannerUrl = function (?string $date = null, ?string $member = null) use ($tabletKey, $publicMode) {
        $params = array_filter([
            'date' => $date,
            'member' => $member,
            'key' => $tabletKey,
        ], fn ($value) => !is_null($value) && $value !== '');

        return ($publicMode ? route('tasks.tablet.public') : route('tasks.tablet')) . (count($params) ? ('?' . http_build_query($params)) : '');
    };

    $memberImageUrl = function ($slug) {
        return route('tasks.tablet.asset.member', ['slug' => $slug]);
    };

    $selectedDateCarbon = \Carbon\Carbon::parse($selectedDate);
@endphp

<div class="fp-app" data-family-planner-app>
    <div class="fp-layout" data-family-planner-layout>
        <aside class="fp-left">
            <section class="fp-panel fp-header-panel">
                <div class="fp-header-copy">
                    <div class="fp-time" data-current-time>{{ now()->format('H:i') }}</div>
                    <div class="fp-date-line" data-current-date>{{ now()->locale('pt_PT')->isoFormat('dddd, D [de] MMMM') }}</div>
                </div>

                <div class="fp-weather-inline">
                    <div class="fp-weather-now">
                        <img src="{{ $weather['image'] }}" alt="{{ $weather['description'] }}">
                        <div class="fp-weather-now-copy">
                            <div class="fp-weather-temp">{{ $weather['temp'] }}°</div>
                            <div class="fp-weather-label">{{ $weather['description'] }}</div>
                            <div class="fp-weather-minmax">Máx {{ $weather['max'] }}° · Mín {{ $weather['min'] }}°</div>
                        </div>

                        <div class="fp-weather-thumbs">
                            @foreach(($weather['hourly'] ?? []) as $slot)
                                <div class="fp-weather-thumb">
                                    <div class="fp-weather-thumb-time">{{ $slot['time'] }}</div>
                                    <img src="{{ $slot['image'] }}" alt="{{ $slot['description'] }}">
                                    <div class="fp-weather-thumb-temp">{{ $slot['temp'] }}°</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="fp-panel fp-calendar-panel">
                <div class="fp-calendar-header">
                    <a href="{{ $buildPlannerUrl($selectedMonth->copy()->subMonthNoOverflow()->toDateString(), $memberParam) }}" class="fp-nav-btn" aria-label="Mês anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <h2>{{ $selectedMonth->copy()->locale('pt_PT')->translatedFormat('F Y') }}</h2>
                    <a href="{{ $buildPlannerUrl($selectedMonth->copy()->addMonthNoOverflow()->toDateString(), $memberParam) }}" class="fp-nav-btn" aria-label="Mês seguinte">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>

                <div class="fp-calendar-grid">
                    @foreach(['S','T','Q','Q','S','S','D'] as $weekday)
                        <div class="fp-weekday">{{ $weekday }}</div>
                    @endforeach

                    @foreach(($calendar['days'] ?? []) as $day)
                        <a href="{{ $buildPlannerUrl($day['date'], $memberParam) }}"
                           class="fp-day {{ $day['is_current_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_selected'] ? 'is-selected' : '' }}">
                            <span>{{ $day['day'] }}</span>
                            @if(!empty($day['event_dots']))
                                <span class="fp-day-dots" aria-hidden="true">
                                    @foreach($day['event_dots'] as $dot)
                                        <span class="fp-day-dot"
                                              style="background-color: {{ $dot['color'] }};"
                                              title="{{ $dot['label'] }}"></span>
                                    @endforeach
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="fp-panel fp-events-panel">
                <div class="fp-section-head">
                    <div>
                        <h3>Eventos do dia</h3>
                        <p>{{ $selectedDateCarbon->locale('pt_PT')->isoFormat('D [de] MMMM') }}</p>
                    </div>

                    <button type="button" class="fp-action-btn" data-bs-toggle="modal" data-bs-target="#familyHubEventModal">
                        <i class="fa-solid fa-plus"></i>
                        <span>Novo evento</span>
                    </button>
                </div>

                <div class="fp-events-list">
                    @forelse(($eventsForSelectedDate ?? []) as $event)
                        <div class="fp-event-row">
                            <div class="fp-event-time">{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i') : '--:--' }}</div>
                            <div class="fp-event-body">
                                <div class="fp-event-title">{{ $event->title }}</div>
                                <div class="fp-event-meta">
                                    @if($event->member)
                                        {{ $event->member->name }}
                                        @if($event->description) · @endif
                                    @endif
                                    {{ $event->description }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="fp-empty-state">Sem eventos para o dia selecionado.</div>
                    @endforelse
                </div>
            </section>
        </aside>

        <section class="fp-right">
            <div class="fp-panel fp-members-panel">
                <div class="fp-members-grid">
                    @foreach(($memberSummaries ?? []) as $summary)
                        <a href="{{ $buildPlannerUrl($selectedDate, $summary->slug) }}"
                           class="fp-member-card {{ $selectedMember && $selectedMember->id === $summary->member->id ? 'is-active' : '' }}">
                            <div class="fp-member-photo-wrap" style="border-radius: 999px;">
                                <img style="border-radius: 999px; border: 4px solid rgb(214,177,107);" src="{{asset('modules/tasks/members')}}/{{ $summary->member->slug }}.jpg" alt="{{ $summary->member->name }}">
                            </div>
                            <div class="fp-member-name">{{ $summary->member->name }}</div>
                            <div class="fp-member-counters">
                                <span>{{ $summary->today_done }}/{{ $summary->today_total }} hoje</span>
                                <span>{{ round($summary->month_percent) }}%</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="fp-panel fp-main-panel">
                @if(!$selectedMember)
                    <div class="fp-thought-wrap">
                        <div class="fp-thought-kicker">Frase motivacional do dia</div>
                        <blockquote class="fp-thought-quote">“{{ $thought['quote'] ?? '' }}”</blockquote>
                        <div class="fp-thought-author">{{ $thought['author'] ?? 'Family Hub' }}</div>
                    </div>
                @else
                    <div class="fp-section-head">
                        <div>
                            <h3>Tarefas de {{ $selectedMember->name }}</h3>
                            <p>{{ $selectedDateCarbon->locale('pt_PT')->isoFormat('D [de] MMMM') }}</p>
                        </div>
                        <a href="{{ $buildPlannerUrl($selectedDate, null) }}" class="fp-clear-member">Fechar</a>
                    </div>

                    <div class="fp-task-list">
                        @forelse(($selectedTasks ?? []) as $task)
                            @include('tasks::partials.task_row', ['task' => $task])
                        @empty
                            <div class="fp-empty-state">Sem tarefas para este membro no dia selecionado.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<button type="button" class="fp-fab-event" data-bs-toggle="modal" data-bs-target="#familyHubEventModal" aria-label="Criar novo evento">
    <i class="fa-solid fa-plus"></i>
</button>

<div class="modal fade" id="familyHubEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down fp-event-modal-dialog">
        <div class="modal-content fp-modal-content">
            <form method="POST" action="{{ $eventStoreRoute }}" class="fp-event-form">
                @csrf
                @if($publicMode && $tabletKey)
                    <input type="hidden" name="key" value="{{ $tabletKey }}">
                @endif

                <div class="modal-header">
                    <h5 class="modal-title">Novo evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Membro</label>
                        <select class="form-select" name="member_id">
                            <option value="">Sem membro específico</option>
                            @foreach(($members ?? []) as $member)
                                <option value="{{ $member->id }}" {{ $selectedMember && $selectedMember->id === $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control" name="title" required data-event-title-input>
                    </div>

                    <div class="row g-3">
                        <div class="col-7">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" name="event_date" value="{{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('Y-m-d') : now()->format('Y-m-d') }}" required>       
                        </div>
                        <div class="col-5">
                            <label class="form-label">Hora</label>
                            <input type="time" class="form-control" name="event_time">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" rows="3" name="description"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.familyPlannerTaskToggleRoute = @json($taskToggleRoute);
    window.familyPlannerSelectedDate = @json($selectedDate);
    window.familyPlannerTabletKey = @json($tabletKey);
</script>
@endsection
