<?php

namespace Modules\Budget\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Modules\Budget\Models\BudgetCategory;
use Modules\Budget\Models\BudgetExpense;
use Modules\Budget\Models\BudgetExpenseDetail;
use Modules\Budget\Models\BudgetIncome;
use Modules\Budget\Models\BudgetObjective;

class BudgetController extends Controller
{
    private static float $forecast = 2216.16;

    public array $actions = [];
    public array $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'Budget', 'url' => route('budget.index')];
    }

    public function index(Request $request)
    {
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));

        $this->actions = $this->buildActions($year, $month, 'overview');

        $totalIncome = BudgetIncome::where('year', $year)
            ->where('month', $month)
            ->sum('amount');

        $expenses = BudgetExpense::where('year', $year)
            ->where('month', $month)
            ->whereNotIn('category', ['potes'])
            ->sum('amount');

        $potes = BudgetExpense::where('year', $year)
            ->where('month', $month)
            ->where('category', 'potes')
            ->sum('amount');

        self::$forecast = (float) BudgetCategory::where('forecast_year', $year)
            ->where('type', 'expense')
            ->where('id_parent', '>', 0)
            ->where('id_parent', '<>', 44)
            ->sum('forecast');

        $alreadySpentPercent = BudgetExpense::getSpentPercentMonth($year, $month);
        $categoryReport = $this->getCategoryReportData($year, $month);
        $monthlyEvolution = $this->getMonthlyEvolutionData($year);
        $topSubcategories = $this->getSubcategoryReportData($year, $month)->take(8)->values();

        $data = [
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'year' => $year,
            'month' => $month,
            'total_income' => $totalIncome,
            'bruno_salary' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary'])->sum('amount'),
            'bruno_asm' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_asm'])->sum('amount'),
            'bruno_cv' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_cv'])->sum('amount'),
            'bruno_income' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary', 'bruno_asm', 'bruno_cv'])->sum('amount'),
            'marcia_salary' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_salary'])->sum('amount'),
            'marcia_oriflame' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_oriflame'])->sum('amount'),
            'marcia_income' => BudgetIncome::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_salary', 'marcia_oriflame'])->sum('amount'),
            'extra_income' => BudgetIncome::where('year', $year)->where('month', $month)->where('category', 'extra_income')->sum('amount'),
            'meninas_income' => BudgetIncome::where('year', $year)->where('month', $month)->where('category', 'meninas_income')->sum('amount'),
            'potes' => $potes,
            'forecast' => self::$forecast,
            'expenses' => $expenses,
            'differences' => (self::$forecast - $expenses),
            'budget_percentage' => self::$forecast > 0 ? (1 - ($expenses / self::$forecast)) * 100 : 0,
            'budget_max' => -((self::$forecast * 1.1) - $expenses),
            'savings' => $totalIncome - $expenses - 700,
            'budget' => (object) self::createMonthlyBudget($year, $month),
            'status_year' => BudgetExpense::getStatus($year, $month),
            'details' => BudgetExpenseDetail::getDetails($year, $month),
            'isMobile' => self::isMobile($request),
            'spent' => $alreadySpentPercent,
            'objectives_short' => BudgetObjective::getObjectives(1, $year, $month),
            'objectives_medium' => BudgetObjective::getObjectives(2, $year, $month),
            'objectives_long' => BudgetObjective::getObjectives(3, $year, $month),
            'report_category_rows' => $categoryReport,
            'chart_category_labels' => $categoryReport->pluck('category_name')->values(),
            'chart_category_forecast' => $categoryReport->pluck('forecast')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_category_expense' => $categoryReport->pluck('amount')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_month_labels' => $monthlyEvolution->pluck('month_label')->values(),
            'chart_month_income' => $monthlyEvolution->pluck('income')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_month_expense' => $monthlyEvolution->pluck('expense')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_top_subcategory_labels' => $topSubcategories->pluck('subcategory_name')->values(),
            'chart_top_subcategory_amounts' => $topSubcategories->pluck('amount')->map(fn ($v) => round((float) $v, 2))->values(),
        ];

        return View::make('budget::pages.index')->with($data);
    }

    public function categoryReport(Request $request)
    {
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));
        $this->actions = $this->buildActions($year, $month, 'category');

        $rows = $this->getCategoryReportData($year, $month);
        $summary = [
            'forecast' => round((float) $rows->sum('forecast'), 2),
            'expense' => round((float) $rows->sum('amount'), 2),
            'difference' => round((float) $rows->sum('difference'), 2),
        ];
        $summary['usage_percent'] = $summary['forecast'] > 0 ? round(($summary['expense'] / $summary['forecast']) * 100, 2) : 0;

        return View::make('budget::pages.category-report')->with([
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'year' => $year,
            'month' => $month,
            'rows' => $rows,
            'summary' => $summary,
            'chart_labels' => $rows->pluck('category_name')->values(),
            'chart_forecast' => $rows->pluck('forecast')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_expense' => $rows->pluck('amount')->map(fn ($v) => round((float) $v, 2))->values(),
        ]);
    }

    public function subcategoryReport(Request $request)
    {
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));
        $selectedCategory = $request->query('category');
        $this->actions = $this->buildActions($year, $month, 'subcategory');

        $rows = $this->getSubcategoryReportData($year, $month, $selectedCategory);
        $categories = BudgetCategory::where('forecast_year', $year)
            ->where('type', 'expense')
            ->where('id_parent', 0)
            ->orderBy('name')
            ->get(['slug', 'name']);

        return View::make('budget::pages.subcategory-report')->with([
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'year' => $year,
            'month' => $month,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'rows' => $rows,
            'chart_labels' => $rows->pluck('subcategory_name')->values(),
            'chart_amounts' => $rows->pluck('amount')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_forecast' => $rows->pluck('forecast')->map(fn ($v) => round((float) $v, 2))->values(),
        ]);
    }

    public function annualAnalysis(Request $request)
    {
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('m'));
        $this->actions = $this->buildActions($year, $month, 'annual');

        $monthlyEvolution = $this->getMonthlyEvolutionData($year);
        $annualCategories = $this->getAnnualCategoryData($year);

        return View::make('budget::pages.annual-analysis')->with([
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'year' => $year,
            'month' => $month,
            'monthlyRows' => $monthlyEvolution,
            'annualCategoryRows' => $annualCategories,
            'chart_month_labels' => $monthlyEvolution->pluck('month_label')->values(),
            'chart_month_income' => $monthlyEvolution->pluck('income')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_month_expense' => $monthlyEvolution->pluck('expense')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_month_balance' => $monthlyEvolution->pluck('balance')->map(fn ($v) => round((float) $v, 2))->values(),
            'chart_annual_category_labels' => $annualCategories->pluck('category_name')->values(),
            'chart_annual_category_amounts' => $annualCategories->pluck('amount')->map(fn ($v) => round((float) $v, 2))->values(),
        ]);
    }

    protected static function isMobile(Request $request): bool
    {
        return (bool) preg_match('/Mobile|Android|iP(hone|od|ad)|IEMobile|BlackBerry|Kindle|Opera Mini|webOS/', (string) $request->header('User-Agent'));
    }

    public function updateData(Request $request)
    {
        $data = null;

        if ($request->type === 'income') {
            $data = BudgetIncome::updateDataIncome($request);
        }

        if ($request->type === 'expense') {
            $data = BudgetExpense::updateDataExpense($request);
        }

        if ($request->type === 'addDetail') {
            $data = BudgetExpenseDetail::addDataDetail($request);
        }

        return $data;
    }

    public function updateForecastData(Request $request)
    {
        $year = (int) $request->year;
        $month = (int) $request->month;

        BudgetExpense::where('year', $year)
            ->where('month', $month)
            ->where('sub_category', $request->tag)
            ->update(['forecast' => $request->value]);

        BudgetCategory::where('forecast_year', $year)
            ->where('slug', $request->tag)
            ->update(['forecast' => $request->value]);

        return response()->json([
            'status' => 'success',
            'message' => 'Forecast atualizado com sucesso.',
        ]);
    }

    public function updateDetail(Request $request)
    {
        BudgetExpenseDetail::updateDetail($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Row updated!',
        ]);
    }

    public function deleteDetail(Request $request)
    {
        BudgetExpenseDetail::deleteDetail($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Row deleted!',
        ]);
    }

    public function addObjective(Request $request)
    {
        return BudgetObjective::createRow($request);
    }

    public function setObjectiveAsDone(Request $request)
    {
        return BudgetObjective::done($request);
    }

    public static function createMonthlyBudget(int $year, int $month): array
    {
        return [
            'income' => self::getIncomeArray($year, $month),
            'expense' => self::getExpenseArray($year, $month),
        ];
    }

    public static function getIncomeArray(int $year, int $month): array
    {
        $structure = [];
        $incomeParents = BudgetCategory::where('forecast_year', $year)
            ->where('type', 'income')
            ->where('id_parent', 0)
            ->get();

        foreach ($incomeParents as $parent) {
            $sons = BudgetCategory::where('forecast_year', $year)
                ->where('type', 'income')
                ->where('id_parent', $parent->id)
                ->get();

            $sonsDetail = [];

            foreach ($sons as $son) {
                $sonsDetail[$son->slug] = [
                    'name' => $son->name,
                    'slug' => $son->slug,
                    'forecast' => $son->forecast,
                ];
            }

            $structure[] = [
                'name' => $parent->name,
                'slug' => $parent->slug,
                'forecast' => $parent->forecast,
                'sons' => $sonsDetail,
            ];
        }

        return $structure;
    }

    public static function getExpenseArray(int $year, int $month): object
    {
        $structure = [];
        $categoriesTable = (new BudgetCategory())->getTable();
        $expenseTable = (new BudgetExpense())->getTable();

        $expenseParents = BudgetCategory::where('forecast_year', $year)
            ->where('type', 'expense')
            ->where('id_parent', 0)
            ->get();

        foreach ($expenseParents as $parent) {
            $sons = BudgetCategory::select("{$categoriesTable}.name", "{$categoriesTable}.slug", "{$categoriesTable}.forecast", "{$expenseTable}.amount")
                ->leftJoin($expenseTable, function ($join) use ($year, $month, $categoriesTable, $expenseTable) {
                    $join->on("{$expenseTable}.sub_category", '=', "{$categoriesTable}.slug")
                        ->where("{$expenseTable}.year", $year)
                        ->where("{$expenseTable}.month", $month);
                })
                ->where('forecast_year', $year)
                ->where("{$categoriesTable}.type", 'expense')
                ->where("{$categoriesTable}.id_parent", $parent->id)
                ->orderBy("{$categoriesTable}.id")
                ->get();

            $sonsDetail = [];

            foreach ($sons as $son) {
                $sonsDetail[$son->slug] = (object) [
                    'name' => $son->name,
                    'slug' => $son->slug,
                    'forecast' => $son->forecast,
                    'expense' => $son->amount,
                ];
            }

            $structure[$parent->slug] = (object) [
                'name' => $parent->name,
                'slug' => $parent->slug,
                'forecast' => $parent->forecast,
                'sons' => (object) $sonsDetail,
            ];
        }

        return (object) $structure;
    }

    public function newYear()
    {
        // intentionally left available for future controlled year rollover
    }

    protected function buildActions(int $year, int $month, string $current): array
    {
        $base = ['year' => $year, 'month' => $month];

        return [
            [
                'name' => 'Overview',
                'icon' => '<i class="fa-solid fa-wallet"></i>',
                'url' => route('budget.index', $base),
                'class' => $current === 'overview' ? 'btn btn-outline-primary active' : 'btn btn-outline-primary',
            ],
            [
                'name' => 'By Category',
                'icon' => '<i class="fa-solid fa-layer-group"></i>',
                'url' => route('budget.reports.category', $base),
                'class' => $current === 'category' ? 'btn btn-outline-primary active' : 'btn btn-outline-primary',
            ],
            [
                'name' => 'By Subcategory',
                'icon' => '<i class="fa-solid fa-sitemap"></i>',
                'url' => route('budget.reports.subcategory', $base),
                'class' => $current === 'subcategory' ? 'btn btn-outline-primary active' : 'btn btn-outline-primary',
            ],
            [
                'name' => 'Annual Analysis',
                'icon' => '<i class="fa-solid fa-chart-line"></i>',
                'url' => route('budget.reports.annual', $base),
                'class' => $current === 'annual' ? 'btn btn-outline-primary active' : 'btn btn-outline-primary',
            ],
        ];
    }

    protected function getCategoryReportData(int $year, int $month)
    {
        $categoriesTable = (new BudgetCategory())->getTable();
        $expenseTable = (new BudgetExpense())->getTable();

        return BudgetExpense::query()
            ->from($expenseTable)
            ->leftJoin($categoriesTable . ' as parent_category', function ($join) use ($expenseTable, $year) {
                $join->on('parent_category.slug', '=', $expenseTable . '.category')
                    ->where('parent_category.type', 'expense')
                    ->where('parent_category.id_parent', 0)
                    ->where('parent_category.forecast_year', $year);
            })
            ->where($expenseTable . '.year', $year)
            ->where($expenseTable . '.month', $month)
            ->selectRaw($expenseTable . '.category as category_slug')
            ->selectRaw('COALESCE(parent_category.name, ' . $expenseTable . '.category) as category_name')
            ->selectRaw('SUM(' . $expenseTable . '.forecast) as forecast')
            ->selectRaw('SUM(' . $expenseTable . '.amount) as amount')
            ->groupBy($expenseTable . '.category', 'parent_category.name')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($row) {
                $forecast = (float) $row->forecast;
                $amount = (float) $row->amount;
                $row->difference = round($forecast - $amount, 2);
                $row->usage_percent = $forecast > 0 ? round(($amount / $forecast) * 100, 2) : 0;
                return $row;
            });
    }

    protected function getSubcategoryReportData(int $year, int $month, ?string $category = null)
    {
        $categoriesTable = (new BudgetCategory())->getTable();
        $expenseTable = (new BudgetExpense())->getTable();

        $query = BudgetExpense::query()
            ->from($expenseTable)
            ->leftJoin($categoriesTable . ' as child_category', function ($join) use ($expenseTable, $year) {
                $join->on('child_category.slug', '=', $expenseTable . '.sub_category')
                    ->where('child_category.type', 'expense')
                    ->where('child_category.forecast_year', $year);
            })
            ->leftJoin($categoriesTable . ' as parent_category', function ($join) use ($expenseTable, $year) {
                $join->on('parent_category.slug', '=', $expenseTable . '.category')
                    ->where('parent_category.type', 'expense')
                    ->where('parent_category.id_parent', 0)
                    ->where('parent_category.forecast_year', $year);
            })
            ->where($expenseTable . '.year', $year)
            ->where($expenseTable . '.month', $month)
            ->selectRaw($expenseTable . '.category as category_slug')
            ->selectRaw('COALESCE(parent_category.name, ' . $expenseTable . '.category) as category_name')
            ->selectRaw($expenseTable . '.sub_category as subcategory_slug')
            ->selectRaw('COALESCE(child_category.name, ' . $expenseTable . '.sub_category) as subcategory_name')
            ->selectRaw('SUM(' . $expenseTable . '.forecast) as forecast')
            ->selectRaw('SUM(' . $expenseTable . '.amount) as amount')
            ->groupBy($expenseTable . '.category', 'parent_category.name', $expenseTable . '.sub_category', 'child_category.name')
            ->orderByDesc('amount');

        if (!empty($category)) {
            $query->where($expenseTable . '.category', $category);
        }

        return $query->get()->map(function ($row) {
            $forecast = (float) $row->forecast;
            $amount = (float) $row->amount;
            $row->difference = round($forecast - $amount, 2);
            $row->usage_percent = $forecast > 0 ? round(($amount / $forecast) * 100, 2) : 0;
            return $row;
        });
    }

    protected function getMonthlyEvolutionData(int $year)
    {
        $rows = collect();

        for ($month = 1; $month <= 12; $month++) {
            $income = (float) BudgetIncome::where('year', $year)->where('month', $month)->sum('amount');
            $expense = (float) BudgetExpense::where('year', $year)->where('month', $month)->sum('amount');
            $rows->push((object) [
                'month' => $month,
                'month_label' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'income' => round($income, 2),
                'expense' => round($expense, 2),
                'balance' => round($income - $expense, 2),
            ]);
        }

        return $rows;
    }

    protected function getAnnualCategoryData(int $year)
    {
        $categoriesTable = (new BudgetCategory())->getTable();
        $expenseTable = (new BudgetExpense())->getTable();

        return BudgetExpense::query()
            ->from($expenseTable)
            ->leftJoin($categoriesTable . ' as parent_category', function ($join) use ($expenseTable, $year) {
                $join->on('parent_category.slug', '=', $expenseTable . '.category')
                    ->where('parent_category.type', 'expense')
                    ->where('parent_category.id_parent', 0)
                    ->where('parent_category.forecast_year', $year);
            })
            ->where($expenseTable . '.year', $year)
            ->selectRaw($expenseTable . '.category as category_slug')
            ->selectRaw('COALESCE(parent_category.name, ' . $expenseTable . '.category) as category_name')
            ->selectRaw('SUM(' . $expenseTable . '.forecast) as forecast')
            ->selectRaw('SUM(' . $expenseTable . '.amount) as amount')
            ->groupBy($expenseTable . '.category', 'parent_category.name')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($row) {
                $forecast = (float) $row->forecast;
                $amount = (float) $row->amount;
                $row->difference = round($forecast - $amount, 2);
                $row->usage_percent = $forecast > 0 ? round(($amount / $forecast) * 100, 2) : 0;
                return $row;
            });
    }
}
