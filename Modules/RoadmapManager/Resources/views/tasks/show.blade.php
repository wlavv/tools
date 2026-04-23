@extends('layouts.app')

@section('content')
    @include('roadmap-manager::partials.styles')
    @include('roadmap-manager::partials.alerts')

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="rm-panel">
                <div class="rm-meta-grid">
                    <div class="rm-meta">
                        <div class="rm-meta__label">Project</div>
                        <div class="rm-meta__value">{{ $task->project->name ?? '-' }}</div>
                    </div>

                    <div class="rm-meta">
                        <div class="rm-meta__label">Milestone</div>
                        <div class="rm-meta__value">{{ $task->milestone->name ?? '-' }}</div>
                    </div>

                    <div class="rm-meta">
                        <div class="rm-meta__label">Status</div>
                        <div class="rm-meta__value">{{ $task->status }}</div>
                    </div>

                    <div class="rm-meta">
                        <div class="rm-meta__label">Priority</div>
                        <div class="rm-meta__value">{{ $task->priority }}</div>
                    </div>

                    <div class="rm-meta rm-form-grid__full">
                        <div class="rm-meta__label">Description</div>
                        <div class="rm-meta__value">{{ $task->description ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rm-panel">
                <div class="rm-title-row" style="margin-bottom:.75rem">
                    <h3 class="rm-title-row__title">Comments</h3>
                </div>

                <form
                    method="POST"
                    action="{{ route('roadmap_manager.tasks.comments.store', $task->id) }}"
                    class="mb-3"
                >
                    @csrf

                    <textarea
                        name="content"
                        class="rm-textarea mb-2"
                        rows="3"
                        placeholder="Add comment"
                    ></textarea>

                    <button class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save</span>
                    </button>
                </form>

                @forelse ($task->comments as $comment)
                    <div class="rm-item-box">{{ $comment->content }}</div>
                @empty
                    <div class="rm-muted">No comments yet.</div>
                @endforelse
            </div>

            <div class="rm-panel">
                <div class="rm-title-row" style="margin-bottom:.75rem">
                    <h3 class="rm-title-row__title">Time Logs</h3>
                </div>

                <form
                    method="POST"
                    action="{{ route('roadmap_manager.tasks.time_logs.store', $task->id) }}"
                    class="rm-form-grid mb-3"
                >
                    @csrf

                    <div>
                        <input
                            type="number"
                            step="0.25"
                            name="logged_hours"
                            class="rm-input"
                            placeholder="Hours"
                        >
                    </div>

                    <div>
                        <input type="date" name="log_date" class="rm-input">
                    </div>

                    <div>
                        <input name="description" class="rm-input" placeholder="Description">
                    </div>

                    <div>
                        <button class="lsg-action-btn lsg-action-btn--primary">Save</button>
                    </div>
                </form>

                <div class="rm-table-wrap">
                    <table class="rm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Hours</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($task->timeLogs as $log)
                                <tr>
                                    <td>{{ optional($log->log_date)->format('Y-m-d') }}</td>
                                    <td>{{ $log->logged_hours }}</td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="rm-muted">No time logs yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="rm-panel">
                <div class="rm-title-row" style="margin-bottom:.75rem">
                    <h3 class="rm-title-row__title">Attachments</h3>
                </div>

                <form
                    method="POST"
                    action="{{ route('roadmap_manager.tasks.attachments.store', $task->id) }}"
                    enctype="multipart/form-data"
                    class="mb-3"
                >
                    @csrf

                    <input type="file" name="file" class="rm-input mb-2">

                    <button class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Upload</span>
                    </button>
                </form>

                <ul class="rm-list">
                    @forelse ($task->attachments as $attachment)
                        <li>{{ $attachment->filename }}</li>
                    @empty
                        <li class="rm-muted">No attachments yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rm-panel">
                <div class="rm-title-row" style="margin-bottom:.75rem">
                    <h3 class="rm-title-row__title">Children & Dependencies</h3>
                </div>

                <div class="rm-stack">
                    <div>
                        <strong>Children:</strong> {{ $task->children->count() }}

                        <ul class="rm-list">
                            @foreach ($task->children as $child)
                                <li>
                                    <a href="{{ route('roadmap_manager.tasks.show', $child->id) }}">
                                        {{ $child->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <strong>Depends on:</strong>

                        <ul class="rm-list">
                            @forelse ($task->dependencies as $dependency)
                                <li>
                                    <a href="{{ route('roadmap_manager.tasks.show', $dependency->id) }}">
                                        {{ $dependency->title }}
                                    </a>
                                </li>
                            @empty
                                <li class="rm-muted">No dependencies.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
