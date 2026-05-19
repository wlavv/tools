<?php

namespace Modules\IdeaLab\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\IdeaLab\Models\IdeaAiTemplate;

class IdeaTemplateController extends Controller
{
    public function index()
    {
        $templates = IdeaAiTemplate::query()->orderBy('entrypoint_type')->orderBy('sort_order')->get();

        return $this->view('idealab::templates.index', compact('templates'));
    }

    public function create()
    {
        return $this->view('idealab::templates.create', ['template' => new IdeaAiTemplate()]);
    }

    public function store(Request $request)
    {
        IdeaAiTemplate::query()->create($this->validated($request));

        return redirect()->route('idealab.templates.index')->with('success', 'Template created.');
    }

    public function edit(IdeaAiTemplate $template)
    {
        $this->setPageTitle('Edit AI Template');

        return $this->view('idealab::templates.edit', compact('template'));
    }

    public function update(Request $request, IdeaAiTemplate $template)
    {
        $template->update($this->validated($request, $template->id));

        return redirect()->route('idealab.templates.index')->with('success', 'Template updated.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:120', 'unique:idealab_ai_templates,key' . ($ignoreId ? ',' . $ignoreId : '')],
            'name' => ['required', 'string', 'max:255'],
            'entrypoint_type' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'system_prompt' => ['nullable', 'string'],
            'user_prompt_template' => ['required', 'string'],
            'supports_chat' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
