<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Modules\Tasks\Models\TaskEvent;
use Modules\Tasks\Services\FamilyPlannerCalendarService;
use Modules\Tasks\Services\FamilyPlannerDashboardService;
use Modules\Tasks\Services\FamilyPlannerEventService;
use Modules\Tasks\Services\FamilyPlannerThoughtService;
use Modules\Tasks\Services\FamilyPlannerWeatherService;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskDone;
use Modules\Tasks\Models\TaskMember;
use Modules\Tasks\Models\TaskRewardLevel;
use Modules\Tasks\Models\TaskRewardOverride;

class TasksController extends Controller
{
    public function __construct()
    {
        // Mantém as rotas públicas do tablet sem autenticação, mas inicializa
        // o mesmo contrato do Controller base: page title, breadcrumbs, actions
        // e View::share(). Não chamamos parent::__construct() porque o parent
        // aplica auth a todas as actions e isso quebraria as rotas públicas.
        $this->middleware('auth')->except([
            'tabletPublic',
            'tabletPublicToggleTask',
            'tabletPublicStoreEvent',
        ]);

        $this->defaultLang = 1;
        Config::set('defaultLang', $this->defaultLang);

        $this->pageTitle       = $this->resolvePageTitle();
        $this->breadcrumbs     = $this->resolveBreadcrumbs();
        $this->showBreadcrumbs = !$this->isDashboardLikeRoute();
        $this->actions         = $this->resolveActions();

        $this->shareLayoutData();
    }

    protected function baseData(array $extra = []): array
    {
        return array_merge([
            'counters' => [],
            'panels' => null,
        ], $extra);
    }

    protected function buildActions(?int $year = null, ?int $month = null): void
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        $this->setActions([
            ['name' => 'Dashboard', 'icon' => '<i class="fa-solid fa-chart-column"></i>', 'url' => route('tasks.dashboard', [$year, $month]), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Calendar', 'icon' => '<i class="fa-solid fa-calendar-days"></i>', 'url' => route('tasks.calendar', [$year, $month]), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Members', 'icon' => '<i class="fa-solid fa-users"></i>', 'url' => route('tasks.members.index'), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Manage Tasks', 'icon' => '<i class="fa-solid fa-list-check"></i>', 'url' => route('tasks.manage.index'), 'class' => 'btn btn-outline-primary'],
            ['name' => 'Rewards', 'icon' => '<i class="fa-solid fa-gift"></i>', 'url' => route('tasks.rewards.index'), 'class' => 'btn btn-outline-primary'],
        ]);
    }

    public function index()
    {
        TaskMember::bootstrapDefaults();
        TaskRewardLevel::bootstrapDefaults();
        TaskDone::ensureMonthSetup();
        $this->buildActions();

        $members = TaskMember::query()->active()->orderBy('sort_order')->orderBy('id')->get();
        $tasks = Task::getGroupedForToday();
        $todayDoneMap = TaskDone::getTodayDoneMap();
        $monthStats = collect(TaskDone::getDashboardStats(now()->year, now()->month)['members'] ?? [])->keyBy('name');
        $memberKeys = $members->mapWithKeys(fn ($member) => [
            $member->name => Str::slug(Str::ascii($member->name), '_'),
        ])->toArray();

        $tasksPageStats = [];
        foreach ($tasks as $name => $group) {
            $stat = $monthStats[$name] ?? null;
            $key = $memberKeys[$name] ?? Str::slug(Str::ascii($name), '_');
            $todayEligibleTotal = $group->filter(fn ($task) => $task->countsForCompletion())->count();
            $todayEligibleDone = $group->filter(function ($task) use ($todayDoneMap) {
                return $task->countsForCompletion() && isset($todayDoneMap[$task->id]) && (int) $todayDoneMap[$task->id]->done === 1;
            })->count();

            $tasksPageStats[$key] = [
                'name' => $name,
                'month_done' => (int) ($stat->total_done ?? 0),
                'month_total' => (int) ($stat->total_rows ?? 0),
                'today_done' => $todayEligibleDone,
                'today_total' => $todayEligibleTotal,
                'month_value' => (float) ($stat->total_value ?? 0),
                'current_streak' => (int) ($stat->current_streak ?? 0),
                'best_streak' => (int) ($stat->best_streak ?? 0),
                'engagement_percent' => (float) ($stat->engagement_percent ?? 0),
                'medal' => [
                    'label' => $stat->medal->label ?? 'Arranque',
                    'icon' => $stat->medal->icon ?? 'fa-regular fa-flag',
                    'class' => $stat->medal->class ?? 'is-base',
                ],
                'rewards' => collect($stat->reward_levels ?? [])->map(fn ($level) => [
                    'threshold_percent' => (float) ($level->threshold_percent ?? 0),
                    'name' => $level->name ?? '',
                    'description' => $level->description ?? '',
                ])->values()->all(),
            ];
        }

        return $this->view('tasks::pages.index', $this->baseData([
            'tasks' => $tasks,
            'todayDoneMap' => $todayDoneMap,
            'members' => $members,
            'monthStatsByName' => $monthStats,
            'memberKeys' => $memberKeys,
            'tasksPageStats' => $tasksPageStats,
        ]));
    }

    public function dashboard(?int $year = null, ?int $month = null)
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;
        TaskRewardLevel::bootstrapDefaults();
        $this->buildActions($year, $month);

        return $this->view('tasks::pages.dashboard.index', $this->baseData([
            'year' => $year,
            'month' => $month,
            'stats' => TaskDone::getDashboardStats($year, $month),
        ]));
    }

    public function calendar(int $year, int $month)
    {
        $this->buildActions($year, $month);


        return $this->view('tasks::pages.calendar', $this->baseData([
            'calendar' => TaskDone::getTasksOf($year, $month),
            'year' => $year,
            'month' => $month,
        ]));
    }


    public function rewards(?int $year = null, ?int $month = null)
    {
        TaskMember::bootstrapDefaults();
        TaskRewardLevel::bootstrapDefaults();

        $year = $year ?: now()->year;
        $month = $month ?: now()->month;
        $this->buildActions($year, $month);

        return $this->view('tasks::pages.rewards.index', $this->baseData([
            'year' => $year,
            'month' => $month,
            'members' => TaskMember::query()->orderByRaw('COALESCE(sort_order, 9999) asc')->orderBy('id')->get(),
            'defaultRewards' => TaskRewardLevel::query()
                ->whereNull('member_id')
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('threshold_percent')
                ->get(),
            'memberRewards' => TaskRewardLevel::query()
                ->whereNotNull('member_id')
                ->orderBy('member_id')
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('threshold_percent')
                ->get()
                ->groupBy('member_id'),
            'monthOverrides' => TaskRewardOverride::query()
                ->where('year', $year)
                ->where('month', $month)
                ->orderByRaw('CASE WHEN member_id IS NULL THEN 0 ELSE 1 END asc')
                ->orderBy('member_id')
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('threshold_percent')
                ->get(),
        ]));
    }

    public function storeRewardLevel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['nullable', 'integer', 'exists:wt_task_members,id'],
            'threshold_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['is_active'] = (int) ($data['is_active'] ?? 1);
        TaskRewardLevel::query()->create($data);

        return redirect()->route('tasks.rewards.index')->with('success', 'Escalão de prémio criado com sucesso.');
    }

    public function updateRewardLevel(Request $request, TaskRewardLevel $reward): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['nullable', 'integer', 'exists:wt_task_members,id'],
            'threshold_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['is_active'] = (int) ($data['is_active'] ?? 0);
        $reward->update($data);

        return redirect()->route('tasks.rewards.index')->with('success', 'Escalão de prémio atualizado com sucesso.');
    }

    public function deleteRewardLevel(TaskRewardLevel $reward): RedirectResponse
    {
        $reward->delete();

        return redirect()->route('tasks.rewards.index')->with('success', 'Escalão de prémio removido com sucesso.');
    }

    public function storeRewardOverride(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'member_id' => ['nullable', 'integer', 'exists:wt_task_members,id'],
            'threshold_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['is_active'] = (int) ($data['is_active'] ?? 1);
        TaskRewardOverride::query()->create($data);

        return redirect()->route('tasks.rewards.index', ['year' => $data['year'], 'month' => $data['month']])->with('success', 'Override mensal criado com sucesso.');
    }

    public function updateRewardOverride(Request $request, TaskRewardOverride $override): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'member_id' => ['nullable', 'integer', 'exists:wt_task_members,id'],
            'threshold_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['is_active'] = (int) ($data['is_active'] ?? 0);
        $override->update($data);

        return redirect()->route('tasks.rewards.index', ['year' => $data['year'], 'month' => $data['month']])->with('success', 'Override mensal atualizado com sucesso.');
    }

    public function deleteRewardOverride(TaskRewardOverride $override): RedirectResponse
    {
        $year = $override->year;
        $month = $override->month;
        $override->delete();

        return redirect()->route('tasks.rewards.index', ['year' => $year, 'month' => $month])->with('success', 'Override mensal removido com sucesso.');
    }

    public function updateDone(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'id' => ['required', 'integer'],
            'type' => ['required', 'integer'],
            'done' => ['required', 'integer'],
            'value' => ['nullable', 'numeric'],
            'date' => ['nullable', 'date'],
        ]);

        return response()->json(TaskDone::updateDoneForDate($payload, $payload['date'] ?? null));
    }

    public function members()
    {
        TaskMember::bootstrapDefaults();
        $this->buildActions();

        return $this->view('tasks::pages.members.index', $this->baseData([
            'members' => TaskMember::query()->orderByRaw('COALESCE(sort_order, 9999) asc')->orderBy('id')->get(),
        ]));
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'task_type' => ['required', 'integer', 'in:1,2'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['slug'] = Str::slug(Str::ascii($data['name']));
        $data['is_active'] = (int) ($data['is_active'] ?? 1);

        TaskMember::query()->create($data);

        return redirect()->route('tasks.members.index')->with('success', 'Membro criado com sucesso.');
    }

    public function updateMember(Request $request, TaskMember $member): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'task_type' => ['required', 'integer', 'in:1,2'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $originalName = $member->name;
        $data['slug'] = Str::slug(Str::ascii($data['name']));
        $data['is_active'] = (int) ($data['is_active'] ?? 0);

        $member->update($data);

        Task::query()->where('member_id', $member->id)->update([
            'name' => $member->name,
            'type' => $member->task_type,
        ]);

        TaskDone::query()->where('name', $originalName)->update(['name' => $member->name]);

        return redirect()->route('tasks.members.index')->with('success', 'Membro atualizado com sucesso.');
    }

    public function deleteMember(TaskMember $member): RedirectResponse
    {
        Task::query()->where('member_id', $member->id)->update(['is_active' => 0]);
        $member->update(['is_active' => 0]);

        return redirect()->route('tasks.members.index')->with('success', 'Membro desativado com sucesso.');
    }

    public function manageTasks()
    {
        TaskMember::bootstrapDefaults();
        $this->buildActions();

        return $this->view('tasks::pages.tasks.index', $this->baseData([
            'members' => TaskMember::query()->active()->orderBy('sort_order')->orderBy('id')->get(),
            'tasksList' => Task::query()->with('member')->orderByRaw('COALESCE(sort_order, 9999) asc')->orderBy('id')->get(),
        ]));
    }

    public function storeTask(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer', 'exists:wt_task_members,id'],
            'task' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'numeric'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
            'days_mask' => ['nullable', 'array'],
            'days_mask.*' => ['integer', 'between:1,7'],
            'frequency' => ['required', 'string', 'in:daily,weekly,monthly'],
            'monthly_day' => ['nullable', 'integer', 'between:1,31'],
            'counts_for_completion' => ['nullable', 'integer', 'in:0,1'],
            'value_mode' => ['nullable', 'string', 'in:add,subtract'],
        ]);

        $member = TaskMember::query()->findOrFail($data['member_id']);
        $data['type'] = $member->task_type;
        $data['name'] = $member->name;
        $data['is_active'] = (int) ($data['is_active'] ?? 1);
        $data['counts_for_completion'] = (int) ($data['counts_for_completion'] ?? 1);
        $data['value_mode'] = $data['value_mode'] ?? 'add';
        $data['days_mask'] = Task::normalizeDaysMaskValue($request->input('days_mask', []));
        if (($data['frequency'] ?? 'daily') !== 'weekly') { $data['days_mask'] = null; }
        if (($data['frequency'] ?? 'daily') !== 'monthly') { $data['monthly_day'] = null; }

        Task::query()->create($data);
        TaskDone::syncMonthSetup();

        return redirect()->route('tasks.manage.index')->with('success', 'Tarefa criada com sucesso.');
    }

    public function updateTask(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer', 'exists:wt_task_members,id'],
            'task' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'numeric'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
            'days_mask' => ['nullable', 'array'],
            'days_mask.*' => ['integer', 'between:1,7'],
            'frequency' => ['required', 'string', 'in:daily,weekly,monthly'],
            'monthly_day' => ['nullable', 'integer', 'between:1,31'],
            'counts_for_completion' => ['nullable', 'integer', 'in:0,1'],
            'value_mode' => ['nullable', 'string', 'in:add,subtract'],
        ]);

        $member = TaskMember::query()->findOrFail($data['member_id']);
        $data['type'] = $member->task_type;
        $data['name'] = $member->name;
        $data['is_active'] = (int) ($data['is_active'] ?? 0);
        $data['counts_for_completion'] = (int) ($data['counts_for_completion'] ?? 0);
        $data['value_mode'] = $data['value_mode'] ?? 'add';
        $data['days_mask'] = Task::normalizeDaysMaskValue($request->input('days_mask', []));
        if (($data['frequency'] ?? 'daily') !== 'weekly') { $data['days_mask'] = null; }
        if (($data['frequency'] ?? 'daily') !== 'monthly') { $data['monthly_day'] = null; }

        $task->update($data);
        TaskDone::query()->where('id_task', $task->id)->update([
            'name' => $member->name,
            'type' => $member->task_type,
        ]);
        TaskDone::syncMonthSetup();

        return redirect()->route('tasks.manage.index')->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function deleteTask(Task $task): RedirectResponse
    {
        $task->update(['is_active' => 0]);
        TaskDone::syncMonthSetup();

        return redirect()->route('tasks.manage.index')->with('success', 'Tarefa desativada com sucesso.');
    }


    public function tablet(Request $request)
    {
        return $this->renderFamilyPlanner($request, false);
    }

    public function tabletPublic(Request $request)
    {
        $this->validateTabletAccess($request);

        return $this->renderFamilyPlanner($request, true);
    }

    public function tabletToggleTask(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'id' => ['required', 'integer', 'exists:wt_tasks,id'],
            'done' => ['required', 'integer', 'in:-1,0,1'],
            'date' => ['required', 'date'],
        ]);

        return response()->json(TaskDone::updateDoneForDate([
            'id' => $payload['id'],
            'type' => 1,
            'done' => $payload['done'],
            'value' => 0,
        ], $payload['date']));
    }

    public function tabletPublicToggleTask(Request $request): JsonResponse
    {
        $this->validateTabletAccess($request);

        return $this->tabletToggleTask($request);
    }

    public function tabletStoreEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['nullable', 'integer', 'exists:wt_task_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
        ]);

        app(FamilyPlannerEventService::class)->create($data);

        return redirect()->back()->with('success', 'Evento criado com sucesso.');
    }

    public function tabletPublicStoreEvent(Request $request): RedirectResponse
    {
        $this->validateTabletAccess($request);

        return $this->tabletStoreEvent($request);
    }

    protected function renderFamilyPlanner(Request $request, bool $publicMode = false)
    {
        TaskMember::bootstrapDefaults();
        TaskRewardLevel::bootstrapDefaults();

        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()));
        TaskDone::ensureMonthSetup($selectedDate->year, $selectedDate->month);

        $selectedMemberKey = $request->query('member');
        $dashboard = app(FamilyPlannerDashboardService::class)->build($selectedDate, $selectedMemberKey);
        $events = app(FamilyPlannerEventService::class)->forMonth($selectedDate->copy()->startOfMonth());
        $calendar = app(FamilyPlannerCalendarService::class)->build(
            $selectedDate,
            $events,
            $dashboard['selectedMember']?->id
        );
        $weather = app(FamilyPlannerWeatherService::class)->today();
        $thought = app(FamilyPlannerThoughtService::class)->today();

        if (! $publicMode) {
            $this->buildActions($selectedDate->year, $selectedDate->month);
        }

        return view('tasks::tablet', $publicMode ? $dashboard + [
            'calendar' => $calendar,
            'selectedDate' => $selectedDate->toDateString(),
            'selectedMonth' => $selectedDate->copy()->startOfMonth(),
            'eventsForSelectedDate' => app(FamilyPlannerEventService::class)->forDate($selectedDate, $dashboard['selectedMember']?->id),
            'weather' => $weather,
            'thought' => $thought,
            'eventStoreRoute' => route('tasks.tablet.public.event.store', ['key' => $request->query('key')]),
            'taskToggleRoute' => route('tasks.tablet.public.task.toggle', ['key' => $request->query('key')]),
            'publicMode' => true,
            'tabletKey' => $request->query('key'),
        ] : $this->baseData($dashboard + [
            'calendar' => $calendar,
            'selectedDate' => $selectedDate->toDateString(),
            'selectedMonth' => $selectedDate->copy()->startOfMonth(),
            'eventsForSelectedDate' => app(FamilyPlannerEventService::class)->forDate($selectedDate, $dashboard['selectedMember']?->id),
            'weather' => $weather,
            'thought' => $thought,
            'eventStoreRoute' => route('tasks.tablet.event.store'),
            'taskToggleRoute' => route('tasks.tablet.task.toggle'),
            'publicMode' => false,
            'tabletKey' => null,
        ]));
    }

    protected function validateTabletAccess(Request $request): void
    {
        $tabletKey = config('tasks.tablet_key', env('TASKS_TABLET_KEY'));
        if (! empty($tabletKey) && $request->query('key') !== $tabletKey) {
            abort(403);
        }
    }
}
