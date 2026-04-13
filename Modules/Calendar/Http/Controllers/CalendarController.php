<?php

namespace Modules\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Calendar\Models\CalendarCategory;
use Modules\Calendar\Models\CalendarContext;
use Modules\Calendar\Models\CalendarEvent;

class CalendarController extends Controller
{
    public array $actions = [];
    public array $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'Calendar', 'url' => route('calendar.index')];
    }

    protected function viewData(array $extra = []): array
    {
        return array_merge([
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
        ], $extra);
    }

    protected function buildActions(): void
    {
        $this->actions = [
            ['name' => 'Dashboard', 'icon' => '<i class="fa-solid fa-calendar-days"></i>', 'url' => route('calendar.index'), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Contexts', 'icon' => '<i class="fa-solid fa-layer-group"></i>', 'url' => route('calendar.contexts.index'), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Categories', 'icon' => '<i class="fa-solid fa-tags"></i>', 'url' => route('calendar.categories.index'), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Events', 'icon' => '<i class="fa-solid fa-calendar-plus"></i>', 'url' => route('calendar.events.index'), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Tablet', 'icon' => '<i class="fa-solid fa-tablet-screen-button"></i>', 'url' => route('calendar.tablet', ['context' => 'family']), 'class' => 'btn btn-outline-primary'],
        ];
    }

    public function index(Request $request)
    {
        $this->buildActions();

        $selectedContext = $request->get('context');

        $contexts = CalendarContext::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $events = CalendarEvent::query()
            ->with(['context', 'category'])
            ->when($selectedContext, function ($query) use ($selectedContext) {
                $query->whereHas('context', function ($q) use ($selectedContext) {
                    $q->where('slug', $selectedContext);
                });
            })
            ->orderBy('start_at')
            ->paginate(30);

        return View::make('calendar::pages.index')->with($this->viewData([
            'contexts' => $contexts,
            'events' => $events,
            'selectedContext' => $selectedContext,
        ]));
    }

    public function tablet(?string $context = 'family')
    {
        $this->buildActions();

        $contextModel = CalendarContext::query()->where('slug', $context)->first();

        $events = CalendarEvent::query()
            ->with(['context', 'category'])
            ->when($contextModel, function ($query) use ($contextModel) {
                $query->where('context_id', $contextModel->id);
            })
            ->orderBy('start_at')
            ->get();

        return View::make('calendar::pages.tablet')->with($this->viewData([
            'context' => $contextModel,
            'events' => $events,
            'contextSlug' => $context,
        ]));
    }

    public function feed(?string $context = null): JsonResponse
    {
        $events = CalendarEvent::query()
            ->with(['context', 'category'])
            ->when($context, function ($query) use ($context) {
                $query->whereHas('context', function ($q) use ($context) {
                    $q->where('slug', $context);
                });
            })
            ->orderBy('start_at')
            ->get()
            ->map(function (CalendarEvent $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'location' => $event->location,
                    'start_at' => optional($event->start_at)->format('Y-m-d H:i:s'),
                    'end_at' => optional($event->end_at)->format('Y-m-d H:i:s'),
                    'all_day' => (bool) $event->all_day,
                    'status' => $event->status,
                    'context' => $event->context?->slug,
                    'context_name' => $event->context?->name,
                    'category' => $event->category?->slug,
                    'category_name' => $event->category?->name,
                ];
            });

        return response()->json($events);
    }

    public function contexts()
    {
        $this->buildActions();

        $contexts = CalendarContext::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        return View::make('calendar::pages.contexts.index')->with($this->viewData([
            'contexts' => $contexts,
        ]));
    }

    public function storeContext(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:calendar_contexts,slug'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = (int) ($data['is_active'] ?? 1);

        CalendarContext::query()->create($data);

        return redirect()->route('calendar.contexts.index')->with('success', 'Contexto criado com sucesso.');
    }

    public function updateContext(Request $request, CalendarContext $context): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:calendar_contexts,slug,' . $context->id],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = (int) ($data['is_active'] ?? 0);

        $context->update($data);

        return redirect()->route('calendar.contexts.index')->with('success', 'Contexto atualizado com sucesso.');
    }

    public function deleteContext(CalendarContext $context): RedirectResponse
    {
        $context->delete();

        return redirect()->route('calendar.contexts.index')->with('success', 'Contexto removido com sucesso.');
    }

    public function categories()
    {
        $this->buildActions();

        $categories = CalendarCategory::query()
            ->with('context')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        $contexts = CalendarContext::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return View::make('calendar::pages.categories.index')->with($this->viewData([
            'categories' => $categories,
            'contexts' => $contexts,
        ]));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'context_id' => ['nullable', 'integer', 'exists:calendar_contexts,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:calendar_categories,slug'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = (int) ($data['is_active'] ?? 1);

        CalendarCategory::query()->create($data);

        return redirect()->route('calendar.categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function updateCategory(Request $request, CalendarCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'context_id' => ['nullable', 'integer', 'exists:calendar_contexts,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:calendar_categories,slug,' . $category->id],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = (int) ($data['is_active'] ?? 0);

        $category->update($data);

        return redirect()->route('calendar.categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function deleteCategory(CalendarCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('calendar.categories.index')->with('success', 'Categoria removida com sucesso.');
    }

    public function events()
    {
        $this->buildActions();

        $events = CalendarEvent::query()
            ->with(['context', 'category'])
            ->orderByDesc('start_at')
            ->paginate(30);

        return View::make('calendar::pages.events.index')->with($this->viewData([
            'events' => $events,
        ]));
    }

    public function createEvent()
    {
        $this->buildActions();

        $contexts = CalendarContext::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = CalendarCategory::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return View::make('calendar::pages.events.create')->with($this->viewData([
            'contexts' => $contexts,
            'categories' => $categories,
        ]));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'context_id' => ['nullable', 'integer', 'exists:calendar_contexts,id'],
            'category_id' => ['nullable', 'integer', 'exists:calendar_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day' => ['nullable', 'integer', 'in:0,1'],
            'status' => ['nullable', 'string', 'max:50'],
            'reference_type' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $data['all_day'] = (int) ($data['all_day'] ?? 0);
        $data['status'] = $data['status'] ?? 'active';
        $data['created_by'] = auth()->id();

        CalendarEvent::query()->create($data);

        return redirect()->route('calendar.events.index')->with('success', 'Evento criado com sucesso.');
    }

    public function showEvent(CalendarEvent $event)
    {
        $this->buildActions();

        $event->load(['context', 'category']);

        $contexts = CalendarContext::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = CalendarCategory::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return View::make('calendar::pages.events.show')->with($this->viewData([
            'event' => $event,
            'contexts' => $contexts,
            'categories' => $categories,
        ]));
    }

    public function updateEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'context_id' => ['nullable', 'integer', 'exists:calendar_contexts,id'],
            'category_id' => ['nullable', 'integer', 'exists:calendar_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day' => ['nullable', 'integer', 'in:0,1'],
            'status' => ['nullable', 'string', 'max:50'],
            'reference_type' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $data['all_day'] = (int) ($data['all_day'] ?? 0);
        $data['status'] = $data['status'] ?? 'active';

        $event->update($data);

        return redirect()->route('calendar.events.show', $event)->with('success', 'Evento atualizado com sucesso.');
    }

    public function deleteEvent(CalendarEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('calendar.events.index')->with('success', 'Evento removido com sucesso.');
    }
}
