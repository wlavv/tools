<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wt_budget_income extends Model
{
    use HasFactory;
    protected $fillable = [
        'year',
        'month',
        'category',
        'amount'
    ];


    public function __construct(){
        $this->table = env('DB2_prefix')."budget_income";
    }
    
    public static function updateDataIncome($request){

        if( !( $request->tag == 'year' ) && !( $request->tag == 'month' )){
            $income = self::updateOrCreate(
                [
                    'year' => $request->year,
                    'month' => $request->month,
                    'category' => $request->tag,
                ],
                [
                    'amount' => $request->value
                ]
            );
        }

        $year = $request->year;
        $month = $request->month;
    
        $total_kpi_income = self::where('year', $year)->where('month', $month)->sum('amount');
        $bruno_income =     self::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary', 'bruno_asm', 'bruno_cv'])->sum('amount');
        $marcia_income =    self::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_salary', 'marcia_oriflame'])->sum('amount');
        $extra_income =     self::where('year', $year)->where('month', $month)->where('category', 'extra_income')->sum('amount');
        $meninas_income =   self::where('year', $year)->where('month', $month)->where('category', 'meninas_income')->sum('amount');
    
        return response()->json([
            'status' => 'success',
            $request->tag => $request->amount,
            'total' => [
                'total_kpi_income' => $total_kpi_income,
                'total_income' => $total_kpi_income,
                'bruno_income' => $bruno_income,
                'marcia_income' => $marcia_income,
                'extra_income' => $extra_income,
                'meninas_income' => $meninas_income,
            ],
            'message' => 'Orçamento atualizado com sucesso.'
        ]);
    }
    
}
