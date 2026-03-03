@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <div>
                    <div>
                        <h2 style="float: left;">AI Consensus Runs</h2>
                        <div style="float: right;">
                            <a class="btn btn-primary" href="{{ route('aiconsensus.create') }}">New run</a>
                            <a class="btn btn-outline-secondary" href="{{ route('aiconsensus.usage') }}">Usage</a>
                        </div>
                    </div>

                    <table class="table table-striped text-center">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($runs as $r)
                                <tr>
                                    <td>{{ $r->id }}</td>
                                    <td>{{ $r->title }}</td>
                                    <td>{{ $r->template_key }}</td>
                                    <td><span class="badge bg-secondary">{{ $r->status }}</span></td>
                                    <td>{{ $r->created_at }}</td>
                                    <td><a href="{{ route('aiconsensus.show', $r->id) }}"
                                            class="btn btn-sm btn-outline-primary">Open</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $runs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
