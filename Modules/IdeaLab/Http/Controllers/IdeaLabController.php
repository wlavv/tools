<?php

namespace Modules\IdeaLab\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\IdeaLab\Http\Requests\StoreIdeaRequest;
use Modules\IdeaLab\Http\Requests\UpdateIdeaRequest;
use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Models\IdeaCategory;
use Modules\IdeaLab\Models\IdeaTag;
use Modules\IdeaLab\Services\IdeaScoringService;

class IdeaLabController extends Controller
{
    public function index(Request $request)
    {
        $ideas = Idea::query()
            ->with('category')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return $this->view('idealab::ideas.index', compact('ideas'));
    }

    public function create()
    {
        return $this->view('idealab::ideas.create', [
            'idea' => new Idea(),
            'categories' => IdeaCategory::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreIdeaRequest $request, IdeaScoringService $scoring)
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['created_by'] = auth()->id();

        $idea = Idea::query()->create($data);
        $this->syncTags($idea, $request->input('tags'));
        $scoring->refresh($idea);

        return redirect()->route('idealab.show', $idea)->with('success', __('idealab::idealab.idea_created'));
    }

    public function show(Idea $idea)
    {
        $this->setPageTitle($idea->title);
        $idea->load([
            'category',
            'tags',
            'aiRuns.template',
            'aiMessages',
            'aiConsensusRuns.aiConsensusRun.template',
            'aiConsensusRuns.aiConsensusRun.providerResponses.provider',
            'aiConsensusRuns.aiConsensusRun.outputs',
            'aiConsensusRuns.aiConsensusRun.messages',
            'conversions',
            'activityLogs',
        ]);

        return $this->view('idealab::ideas.show', compact('idea'));
    }

    public function edit(Idea $idea)
    {
        $this->setPageTitle('Edit Idea');
        $idea->load('tags');

        return $this->view('idealab::ideas.edit', [
            'idea' => $idea,
            'categories' => IdeaCategory::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateIdeaRequest $request, Idea $idea, IdeaScoringService $scoring)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $idea->update($data);
        $this->syncTags($idea, $request->input('tags'));
        $scoring->refresh($idea);

        return redirect()->route('idealab.show', $idea)->with('success', __('idealab::idealab.idea_updated'));
    }

    public function destroy(Idea $idea)
    {
        $idea->delete();

        return redirect()->route('idealab.index')->with('success', __('idealab::idealab.idea_deleted'));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (Idea::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function syncTags(Idea $idea, ?string $tags): void
    {
        $tagIds = collect(explode(',', (string) $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->map(function ($tag) {
                return IdeaTag::query()->firstOrCreate(
                    ['slug' => Str::slug($tag)],
                    ['name' => $tag]
                )->id;
            })
            ->values()
            ->all();

        $idea->tags()->sync($tagIds);
    }
}
