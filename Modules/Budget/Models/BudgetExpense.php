<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BudgetExpense extends Model
{
    use HasFactory;

    protected $table = 'wt_budget_expense';
    protected $fillable = [
        'year',
        'month',
        'category',
        'sub_category',
        'amount',
        'forecast',
    ];
    public $timestamps = false;

    public static function updateDataExpense($request, $value = 0)
    {
        if ($request->tag !== 'year' && $request->tag !== 'month') {
            self::updateOrCreate(
                [
                    'year' => $request->year,
                    'month' => $request->month,
                    'category' => $request->group,
                    'sub_category' => $request->tag,
                ],
                [
                    'amount' => $value == 0 ? $request->value : $value,
                    'forecast' => $request->forecast,
                ]
            );
        }

        $year = $request->year;
        $month = $request->month;

        $monthTotalExpense = self::where('year', $year)->where('month', $month)->sum('amount');
        $monthTotalForecast = BudgetCategory::where('forecast_year', $year)
            ->where('type', 'expense')
            ->where('id_parent', '>', 0)
            ->where('id_parent', '<>', 44)
            ->sum('forecast');

        $totalForecast = self::where('year', $year)->where('month', $month)->where('category', $request->group)->sum('forecast');
        $totalSpent = self::where('year', $year)->where('month', $month)->where('category', $request->group)->sum('amount');
        $displayPotes = self::where('year', $year)->where('month', $month)->where('category', 'potes')->sum('amount');

        $rowDiffs = [];
        $itemsGroup = self::where('year', $year)->where('month', $month)->where('category', $request->group)->get();

        foreach ($itemsGroup as $item) {
            $rowDiffsValue = $item->amount - $item->forecast;
            $rowDiffsPercent = $item->forecast > 0 ? (1 - ($item->amount / $item->forecast)) * -100 : 100;
            $rowDiffsColor = $rowDiffsValue > 0 ? 'alert alert-danger' : 'alert alert-success';

            $rowDiffs[$item->sub_category] = [$item->amount, $rowDiffsValue, $rowDiffsPercent, $rowDiffsColor];
        }

        $totalDiffsValue = $totalSpent - $totalForecast;
        $totalDiffsPercent = $totalForecast > 0 ? (1 - ($totalSpent / $totalForecast)) * -100 : 0;

        return response()->json([
            'status' => 'success',
            $request->tag => $request->amount,
            'total' => [
                'month_total_expense' => $monthTotalExpense,
                'total_forecast' => $totalForecast,
                'total_spent' => $totalSpent,
                'row_diff' => $rowDiffs,
                'total_diffs_value' => $totalDiffsValue,
                'total_diffs_percent' => $totalDiffsPercent,
                'display_potes' => $displayPotes,
                'display_forecast' => $monthTotalForecast,
                'display_expenses' => $monthTotalExpense,
                'display_difference' => $monthTotalExpense - $monthTotalForecast,
                'display_percentage' => $monthTotalForecast > 0 ? (1 - ($monthTotalExpense / $monthTotalForecast)) * -100 : 0,
                'display_budget_max' => ($monthTotalForecast * 1.1 - $monthTotalExpense),
            ],
            'message' => 'Orçamento atualizado com sucesso.',
        ]);
    }

    public static function getStatus($year, $month)
    {
        $categories = self::where('year', $year)->where('month', $month)->orderBy('category')->groupBy('category')->get();

        if (count($categories) === 0) {
            $newCategories = BudgetCategory::where('forecast_year', $year)
                ->where('id_parent', 0)
                ->where('type', 'expense')
                ->get();

            foreach ($newCategories as $parentCategory) {
                $sonsCategories = BudgetCategory::where('forecast_year', $year)
                    ->where('id_parent', $parentCategory->id)
                    ->where('type', 'expense')
                    ->get();

                foreach ($sonsCategories as $sonCategory) {
                    $newExpense = new self();
                    $newExpense->category = $parentCategory->slug;
                    $newExpense->sub_category = $sonCategory->slug;
                    $newExpense->forecast = $sonCategory->forecast;
                    $newExpense->amount = 0;
                    $newExpense->year = $year;
                    $newExpense->month = $month;
                    $newExpense->save();
                }
            }

            $categories = self::where('year', $year)->where('month', $month)->orderBy('category')->groupBy('category')->get();
        }

        $statusByCategory = [];

        foreach ($categories as $category) {
            $subCategories = self::leftJoin('wt_budget_categories', function ($join) {
                    $join->on('wt_budget_expense.sub_category', '=', 'wt_budget_categories.slug')
                        ->where('type', 'expense');
                })
                ->where('year', $year)
                ->where('month', $month)
                ->where('category', $category->category)
                ->orderBy('sub_category')
                ->get();

            $statusBySubCategory = [];

            foreach ($subCategories as $subCategory) {
                $forecast = $subCategory->forecast * $month;
                $expense = self::where('year', $year)
                    ->where('month', '<', ($month + 1))
                    ->where('sub_category', $subCategory->sub_category)
                    ->sum('amount');
                $percentage = $forecast == 0 ? 0 : ($expense / $forecast) * 100;

                $statusBySubCategory[$subCategory->sub_category] = [
                    'name' => $subCategory->name,
                    'forecast' => $forecast,
                    'expense' => $expense,
                    'percentage' => -(100 - $percentage),
                    'icon' => $percentage > 100 ? '<i class="fa-solid fa-arrow-up" style="color: red;"></i>' : '<i class="fa-solid fa-arrow-down" style="color: green;"></i>',
                    'color' => $percentage > 100 ? 'red' : 'green',
                ];
            }

            $subCategoryTotal = self::select(DB::raw('SUM(forecast) AS forecast'), DB::raw('SUM(amount) AS amount'))
                ->where('year', $year)
                ->where('month', '<', ($month + 1))
                ->where('category', $category->category)
                ->first();

            $forecast = $subCategoryTotal->forecast;
            $expense = $subCategoryTotal->amount;
            $percentage = $forecast == 0 ? 0 : ($expense / $forecast) * 100;

            $statusBySubCategory['total'] = [
                'name' => 'TOTAL',
                'forecast' => $forecast,
                'expense' => $expense,
                'percentage' => -(100 - $percentage),
                'icon' => $percentage > 100 ? '<i class="fa-solid fa-arrow-up" style="color: red;"></i>' : '<i class="fa-solid fa-arrow-down" style="color: green;"></i>',
                'color' => $percentage > 100 ? 'red' : 'green',
            ];

            $statusByCategory[$category->category] = $statusBySubCategory;
        }

        return $statusByCategory;
    }

    public static function getSpentPercentMonth($year, $month)
    {
        $array = [];

        $data = self::select('category', DB::raw('sum(forecast) AS forecast'), DB::raw('sum(amount) AS amount'), DB::raw('(sum(amount)/sum(forecast))*100 AS percent'))
            ->where('year', $year)
            ->where('month', $month)
            ->groupBy('category')
            ->get();

        foreach ($data as $element) {
            $array[$element->category] = [
                'detail' => $element,
                'color' => self::getColor($element->percent),
            ];
        }

        return $array;
    }

    private static function getColor($percent)
    {
        $greenValue = max(0, 255 - min(255, ($percent / 100) * 255));
        $redValue = min(255, ($percent / 100) * 255);

        return "rgba($redValue, $greenValue, 0, 0.2)";
    }

    public static function getCurrentByCategories($year, $month)
    {
        $expensesData = [];
        $expensesCategories = self::groupBy('category')->get();

        foreach ($expensesCategories as $category) {
            $expensesSubCategories = self::where('category', $category->category)
                ->where('year', $year)
                ->where('month', '<', $month + 1)
                ->get();

            foreach ($expensesSubCategories as $subCategory) {
                $expensesData[$category->category][$subCategory->sub_category] = self::select('category', 'sub_category', DB::raw('SUM(forecast) AS forecast'), DB::raw('SUM(amount) AS amount'))
                    ->where('category', $subCategory->category)
                    ->where('sub_category', $subCategory->sub_category)
                    ->where('year', $year)
                    ->where('month', '<', $month + 1)
                    ->first();
            }
        }

        return $expensesData;
    }
}
