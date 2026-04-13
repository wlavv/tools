<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetIncome extends Model
{
    use HasFactory;

    protected $table = 'wt_budget_income';
    protected $fillable = [
        'year',
        'month',
        'category',
        'amount',
    ];
    public $timestamps = false;

    public static function updateDataIncome($request)
    {
        if ($request->tag !== 'year' && $request->tag !== 'month') {
            self::updateOrCreate(
                [
                    'year' => $request->year,
                    'month' => $request->month,
                    'category' => $request->tag,
                ],
                [
                    'amount' => $request->value,
                ]
            );
        }

        $year = $request->year;
        $month = $request->month;

        $totalKpiIncome = self::where('year', $year)->where('month', $month)->sum('amount');
        $brunoIncome = self::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary', 'bruno_asm', 'bruno_cv'])->sum('amount');
        $marciaIncome = self::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_salary', 'marcia_oriflame'])->sum('amount');
        $extraIncome = self::where('year', $year)->where('month', $month)->where('category', 'extra_income')->sum('amount');
        $meninasIncome = self::where('year', $year)->where('month', $month)->where('category', 'meninas_income')->sum('amount');

        return response()->json([
            'status' => 'success',
            $request->tag => $request->amount,
            'total' => [
                'total_kpi_income' => $totalKpiIncome,
                'total_income' => $totalKpiIncome,
                'bruno_income' => $brunoIncome,
                'marcia_income' => $marciaIncome,
                'extra_income' => $extraIncome,
                'meninas_income' => $meninasIncome,
            ],
            'message' => 'Orçamento atualizado com sucesso.',
        ]);
    }
}
