<?php

namespace Modules\ERP\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ERP\Models\ERPConfiguration;
use Modules\ERP\Models\ERPDocumentType;
use Modules\ERP\Models\ERPStatus;
use Modules\ERP\Models\ERPWorkflow;
use Modules\ERP\Models\ERPNumberingSequence;
use Modules\ERP\Models\ERPTimelineTask;

class ERPSettingsController extends Controller
{
    public function index()
    {
        return $this->view('erp::settings.index', [
            'configs' => ERPConfiguration::query()->orderBy('group')->orderBy('key')->get(),
            'documentTypes' => ERPDocumentType::query()->orderBy('sort_order')->get(),
            'statuses' => ERPStatus::query()->orderBy('scope')->orderBy('sort_order')->get(),
            'workflows' => ERPWorkflow::query()->with(['fromStatus', 'toStatus'])->latest()->limit(25)->get(),
            'sequences' => ERPNumberingSequence::query()->orderBy('document_type_code')->get(),
            'timelineTasks' => ERPTimelineTask::query()->orderBy('step_key')->orderBy('sort_order')->get(),
        ]);
    }

    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'group' => ['required', 'string', 'max:80'],
            'key' => ['required', 'string', 'max:120'],
            'value' => ['nullable'],
            'type' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string'],
        ]);

        ERPConfiguration::updateOrCreate(
            ['group' => $data['group'], 'key' => $data['key']],
            [
                'value' => $data['value'] ?? null,
                'type' => $data['type'] ?? 'string',
                'description' => $data['description'] ?? null,
            ]
        );

        return redirect()->route('erp.settings.index')->with('success', __('erp::messages.config_saved'));
    }
}
