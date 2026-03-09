
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('system_logs.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col">
                        <input name="level" class="form-control" placeholder="level (info, warning, error)">
                    </div>
                    <div class="col">
                        <input name="message" class="form-control" placeholder="message">
                    </div>
                    <div class="col">
                        <input name="context" class="form-control" placeholder="context (optional json)">
                    </div>
                    <div class="col">
                        <button class="btn btn-primary">Add Log</button>
                    </div>
                </div>
            </form>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Level</th>
                        <th>Message</th>
                        <th>User</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->level }}</td>
                        <td>{{ $log->message }}</td>
                        <td>{{ $log->user_id }}</td>
                        <td>{{ $log->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
