<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetExpenseDetail extends Model
{
    use HasFactory;

    protected $table = 'wt_budget_expense_details';
    protected $fillable = [
        'slug',
        'detail',
        'year',
        'month',
        'amount',
    ];
    public $timestamps = false;

    public static function getDetails($year, $month)
    {
        $details = [];
        $detailsCategories = self::where('year', $year)->where('month', $month)->groupBy('slug')->get();

        foreach ($detailsCategories as $category) {
            $details[$category->slug] = self::where('year', $year)->where('month', $month)->where('slug', $category->slug)->get();
        }

        return $details;
    }

    public static function addDataDetail($request)
    {
        $insert = new self();
        $insert->slug = $request->tag;
        $insert->detail = $request->detail;
        $insert->year = $request->year;
        $insert->month = $request->month;
        $insert->amount = $request->value;
        $insert->save();

        $value = self::where('year', $request->year)
            ->where('month', $request->month)
            ->where('slug', $request->tag)
            ->sum('amount');

        BudgetExpense::updateDataExpense($request, $value);

        return response()->json([
            'status' => 'success',
            'message' => 'Orçamento atualizado com sucesso.',
        ]);
    }

    public static function updateDetail($request)
    {
        $detail = self::where('id', $request->id_detail)->first();
        $detail->amount = $request->value;
        $detail->detail = $request->detail;
        $detail->update();

        $total = self::where('year', $detail->year)->where('month', $detail->month)->where('slug', $detail->slug)->sum('amount');

        BudgetExpense::where('year', $detail->year)
            ->where('month', $detail->month)
            ->where('sub_category', $detail->slug)
            ->update(['amount' => $total]);

        return 1;
    }

    public static function deleteDetail($request)
    {
        $detail = self::where('id', $request->id_detail)->first();
        $value = BudgetExpense::where('year', $detail->year)->where('month', $detail->month)->where('sub_category', $detail->slug)->value('amount');
        $newTotal = $value - $detail->amount;

        BudgetExpense::where('year', $detail->year)
            ->where('month', $detail->month)
            ->where('sub_category', $detail->slug)
            ->update(['amount' => $newTotal]);

        $detail->delete();

        return 1;
    }
}
