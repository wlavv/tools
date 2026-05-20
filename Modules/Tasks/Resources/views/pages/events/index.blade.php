@extends('layouts.app')
@include('tasks::includes.css')

@section('content')
<div class="lsg-content px-0">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="mb-0">Eventos familiares</h3>
                <div class="muted-small">Eventos globais e eventos por membro usados no tablet familiar.</div>
            </div>

            <form method="GET" action="{{ route('tasks.events.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                <input type="month" class="form-control" name="month" value="{{ $selectedMonth->format('Y-m') }}" style="max-width: 170px;">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-filter"></i></button>
                <a href="{{ route('tasks.tablet', ['date' => $selectedMonth->toDateString()]) }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-tablet-screen-button"></i>
                    Tablet
                </a>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h4 class="mb-3">Novo evento</h4>
            <form method="POST" action="{{ route('tasks.events.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Membro</label>
                    <select class="form-select" name="member_id">
                        <option value="">Evento global</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Titulo</label>
                    <input class="form-control" name="title" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data</label>
                    <input type="date" class="form-control" name="event_date" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hora</label>
                    <input type="time" class="form-control" name="event_time">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Cor</label>
                    <input class="form-control" name="color" placeholder="#d6b16b">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-primary w-100"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div class="col-12">
                    <label class="form-label">Descricao</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h4 class="mb-0">{{ $selectedMonth->format('m/Y') }}</h4>
                <span class="badge bg-secondary">{{ $events->count() }} evento(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 lsg-datatable">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Hora</th>
                            <th>Membro</th>
                            <th>Titulo</th>
                            <th>Descricao</th>
                            <th>Cor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                            <tr>
                                <td style="min-width: 150px;">
                                    <input form="update-family-event-{{ $event->id }}" type="date" class="form-control" name="event_date" value="{{ optional($event->event_date)->format('Y-m-d') }}" required>
                                </td>
                                <td style="min-width: 120px;">
                                    <input form="update-family-event-{{ $event->id }}" type="time" class="form-control" name="event_time" value="{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i') : '' }}">
                                </td>
                                <td style="min-width: 180px;">
                                    <select form="update-family-event-{{ $event->id }}" class="form-select" name="member_id">
                                        <option value="">Global</option>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}" @selected((int) $event->member_id === (int) $member->id)>{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width: 220px;">
                                    <input form="update-family-event-{{ $event->id }}" class="form-control" name="title" value="{{ $event->title }}" required>
                                </td>
                                <td style="min-width: 260px;">
                                    <input form="update-family-event-{{ $event->id }}" class="form-control" name="description" value="{{ $event->description }}">
                                </td>
                                <td style="min-width: 120px;">
                                    <input form="update-family-event-{{ $event->id }}" class="form-control" name="color" value="{{ $event->color }}">
                                </td>
                                <td class="text-end" style="min-width: 120px;">
                                    <button form="update-family-event-{{ $event->id }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i></button>
                                    <button type="submit"
                                            form="delete-family-event-{{ $event->id }}"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Remover este evento?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <form id="update-family-event-{{ $event->id }}" method="POST" action="{{ route('tasks.events.update', $event) }}" class="d-none">@csrf</form>
                                    <form id="delete-family-event-{{ $event->id }}" method="POST" action="{{ route('tasks.events.delete', $event) }}" class="d-none">@csrf</form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
