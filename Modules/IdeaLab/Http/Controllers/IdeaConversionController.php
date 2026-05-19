<?php

namespace Modules\IdeaLab\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Services\IdeaConversionService;

class IdeaConversionController extends Controller
{
    public function convert(Idea $idea, IdeaConversionService $conversionService)
    {
        $conversion = $conversionService->convert($idea);

        if ($conversion->project_id) {
            return redirect()
                ->route('project_manager.projects.show', $conversion->project_id)
                ->with('success', 'Idea converted into a Project Manager project with generated milestones and tasks.');
        }

        return redirect()
            ->route('idealab.show', $idea)
            ->with('success', 'Project payload created. Connect Project Manager service to complete automatic creation.');
    }
}
