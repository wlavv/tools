@extends('layouts.app')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Run security validation</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('module-security-validator.run') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Module Name</label>
                        <input type="text" name="module_name" class="form-control" value="{{ old('module_name', 'IdeaLab') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Module Path</label>
                        <input type="text" name="module_path" class="form-control" value="{{ old('module_path', 'Modules/IdeaLab') }}" required>
                        <div class="form-text">Use a relative path from Laravel base path or an absolute path.</div>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-shield-halved me-1"></i> Run Security Check
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <h5 class="card-title">Checks included</h5>
            <div class="row">
                <div class="col-md-4"><i class="fa-solid fa-terminal me-1 text-danger"></i> Shell/process execution</div>
                <div class="col-md-4"><i class="fa-solid fa-file-shield me-1 text-danger"></i> .env/core write attempts</div>
                <div class="col-md-4"><i class="fa-solid fa-route me-1 text-warning"></i> Route protection</div>
                <div class="col-md-4"><i class="fa-solid fa-upload me-1 text-warning"></i> Upload validation</div>
                <div class="col-md-4"><i class="fa-solid fa-folder-tree me-1 text-danger"></i> Path traversal risks</div>
                <div class="col-md-4"><i class="fa-solid fa-database me-1 text-warning"></i> Raw SQL risk patterns</div>
            </div>
        </div>
    </div>
@endsection
