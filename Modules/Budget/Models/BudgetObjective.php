<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetObjective extends Model
{
    use HasFactory;

    protected $table = 'wt_budget_objectives';
    protected $fillable = [
        'name',
        'amount',
        'priority',
        'category',
        'sub_category',
        'type',
        'link',
        'done',
    ];
    public $timestamps = false;

    public static function createRow($request)
    {
        $category = explode('|', (string) $request->objective_income_source);

        $data = [
            'name' => $request->objective_name,
            'amount' => $request->objective_value,
            'priority' => $request->objective_priority,
            'category' => $category[0] ?? '',
            'sub_category' => $category[1] ?? '',
            'type' => $request->objective_type,
            'link' => $request->objective_link,
        ];

        self::updateOrCreate(
            ['id' => $request->objective_id ?? null],
            $data
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Objective inserted!',
        ]);
    }

    public static function getObjectives($type, $year, $month)
    {
        $objectivesData = self::where('type', $type)->where('done', 0)->orderBy('priority')->get();
        $expenses = BudgetExpense::getCurrentByCategories($year, $month);

        foreach ($objectivesData as $data) {
            if (isset($expenses[$data->category]) && isset($expenses[$data->category][$data->sub_category])) {
                $availableAmount = ($expenses[$data->category][$data->sub_category]->forecast - $expenses[$data->category][$data->sub_category]->amount);

                if ($availableAmount > 0) {
                    if ($availableAmount > $data->amount) {
                        $data->available = '100%';
                        $expenses[$data->category][$data->sub_category]->amount += $data->amount;
                        $data->buy = 1;
                    } else {
                        $valueAvailable = $expenses[$data->category][$data->sub_category]->forecast - $expenses[$data->category][$data->sub_category]->amount;
                        $data->available = number_format(($valueAvailable / $data->amount) * 100, 2, '.', ' ') . ' % <br><span style="color: green;">( ' . number_format($valueAvailable, 2, '.', ' ') . ' € )</span>';
                        $expenses[$data->category][$data->sub_category]->amount += $data->amount;
                        $data->buy = 0;
                    }
                }
            }
        }

        return $objectivesData;
    }

    public static function done($request)
    {
        self::where('id', $request->id)->update(['done' => 1]);

        return response()->json([
            'status' => 'success',
            'message' => 'Objective updated!',
        ]);
    }
}
