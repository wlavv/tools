<?php

namespace Modules\DataExportCenter\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\DataExportCenter\Models\DataExportReportTemplate;

class ReportTemplateController extends Controller
{
    public function index()
    {
        return $this->view('data-export-center::templates.index', [
            'templates' => DataExportReportTemplate::query()->latest()->paginate(30),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'profile_key' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'scope_type' => ['required', 'string', 'max:50'],
            'scope_key' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'title_template' => ['nullable', 'string'],
            'header_html' => ['nullable', 'string'],
            'footer_html' => ['nullable', 'string'],
            'body_html' => ['nullable', 'string'],
            'css' => ['nullable', 'string'],
        ]);

        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['engine'] = 'blade';
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        DataExportReportTemplate::query()->updateOrCreate(['key' => $data['key']], $data);

        return redirect()->route('data_export_center.templates.index');
    }
}
