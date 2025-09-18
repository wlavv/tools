<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\webToolsManager\wt_budget_categories;

class wt_budget_expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'year',
        'month',
        'category',
        'sub_category',
        'amount',
        'forecast'
    ];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."budget_expense";
    }
    
    public static function updateDataExpense($request, $value = 0){
        
        if( !( $request->tag == 'year' ) && !( $request->tag == 'month' )){
            $income = self::updateOrCreate(
                [
                    'year' => $request->year,
                    'month' => $request->month,
                    'category' => $request->group,
                    'sub_category' => $request->tag,
                ],
                [
                    'amount' => ( $value == 0) ? $request->value : $value,
                    'forecast' => $request->forecast
                ]
            );
        }
        
        $year = $request->year;
        $month = $request->month;

        $month_total_expense  = self::where('year', $year)->where('month', $month)->sum('amount');
        $month_total_forecast  = wt_budget_categories::where('forecast_year', $year)->where('type', 'expense')->where('id_parent', '>', 0)->where('id_parent', '<>', 44)->sum('forecast');

        $total_forecast = self::where('year', $year)->where('month', $month)->where('category', $request->group)->sum('forecast');
        $total_spent    = self::where('year', $year)->where('month', $month)->where('category', $request->group)->sum('amount');

        $display_potes    = self::where('year', $year)->where('month', $month)->where('category', 'potes')->sum('amount');

        $row_diffs = array();        
        $items_group = self::where('year', $year)->where('month', $month)->where('category', $request->group)->get();
        foreach($items_group AS $item){
            
            
            $row_diffs_value = $item->amount - $item->forecast;
            $row_diffs_percent = ( $item->forecast > 0) ? ( 1 - ( $item->amount / $item->forecast ) ) * -100 : 100;
            $row_diffs_color = ( $row_diffs_value > 0 ) ? 'alert alert-danger' : 'alert alert-success';
            
            $row_diffs[$item->sub_category] = [ $item->amount, $row_diffs_value, $row_diffs_percent, $row_diffs_color];
        }

        $total_diffs_value = $total_spent - $total_forecast;
        $total_diffs_percent = ( 1 - ( $total_spent / $total_forecast ) ) * -100;
        $total_diffs_color = ( $total_diffs_value > 0 ) ? 'alert alert-danger' : 'alert alert-success';

        return response()->json([
            'status' => 'success',
            $request->tag => $request->amount,
            'total' => [
                'month_total_expense' => $month_total_expense,
                'total_forecast' => $total_forecast,
                'total_spent' => $total_spent,
                'row_diff' => $row_diffs,
                'total_diffs_value' => $total_diffs_value,
                'total_diffs_percent' => $total_diffs_percent,
                'display_potes' => $display_potes,
                'display_forecast' => $month_total_forecast,
                'display_expenses' => $month_total_expense,
                'display_difference' => $month_total_expense - $month_total_forecast,
                'display_percentage' => ( 1 - ( $month_total_expense / $month_total_forecast ) ) * -100,
                'display_budget_max' => ( $month_total_forecast * 1.1 - $month_total_expense)
            ],
            'message' => 'Orçamento atualizado com sucesso.'
        ]);
        
    }    
    
    public static function getStatus($year, $month){
        
        $categories= self::where('year', $year)->where('month', $month)->orderBy('category')->groupBy('category')->get();
        
        if(count($categories) == 0){
            
            $new_categories = wt_budget_categories::where('forecast_year', $year)->where('id_parent', 0)->where('type', 'expense')->get();
            
            foreach($new_categories AS $parent_category){

                $sons_categories = wt_budget_categories::where('forecast_year', $year)->where('id_parent', $parent_category->id)->where('type', 'expense')->get();

                foreach($sons_categories AS $son_category){
                    
                    $new_expense = new wt_budget_expense();
                    $new_expense->category = $parent_category->slug;
                    $new_expense->sub_category = $son_category->slug;
                    $new_expense->forecast = $son_category->forecast;
                    $new_expense->amount = 0;
                    $new_expense->year = $year;
                    $new_expense->month = $month;
                    $new_expense->save();
                    
                }
            }

            $categories= self::where('year', $year)->where('month', $month)->orderBy('category')->groupBy('category')->get();
        }

        $statusByCategory = array();

        foreach($categories AS $category){
            
            $sub_categories = self::leftJoin('wt_budget_categories', function($join) {
                    $join->on('wt_budget_expense.sub_category', '=', 'wt_budget_categories.slug')->where('type', 'expense');
                })
                ->where('year', $year)
                ->where('month', $month)
                ->where('category', $category->category)
                ->orderBy('sub_category')
                ->get();

            
            $statusBySubCategory = array();
            
            foreach($sub_categories AS $sub_category){
    
                $forecast = $sub_category->forecast * $month;
                $expense = self::where('year', $year)->where('month', '<', ($month+1))->where('sub_category', $sub_category->sub_category)->sum('amount');
                $percentage = ( $forecast == 0 ) ? 0 : ( $expense / $forecast ) * 100;
                

                
                $statusBySubCategory[$sub_category->sub_category] = [
                    'name' => $sub_category->name,
                    'forecast' => $forecast,
                    'expense' => $expense,
                    'percentage' => -(100 - $percentage),
                    'icon' => ( $percentage > 100 ) ? '<i class="fa-solid fa-arrow-up" style="color: red;"></i>' : '<i class="fa-solid fa-arrow-down" style="color: green;"></i>',
                    'color' => ( $percentage > 100 ) ? 'red' : 'green'
                    ];
            }
    
            $sub_category_total= self::select( DB::raw('SUM(forecast) AS forecast'), DB::raw('SUM(amount) AS amount') )->where('year', $year)->where('month', '<', ($month+1))->where('category', $category->category)->first();
            
            $forecast = $sub_category_total->forecast;
            $expense = $sub_category_total->amount;
            $percentage = ( $forecast == 0 ) ? 0 : ( $expense / $forecast ) * 100;
            
            $statusBySubCategory['total'] = [
                'name' => 'TOTAL',
                'forecast' => $forecast,
                'expense' => $expense,
                'percentage' => -(100 - $percentage),
                'icon' => ( $percentage > 100 ) ? '<i class="fa-solid fa-arrow-up" style="color: red;"></i>' : '<i class="fa-solid fa-arrow-down" style="color: green;"></i>',
                'color' => ( $percentage > 100 ) ? 'red' : 'green'
                ];
                
            $statusByCategory[$category->category] = $statusBySubCategory;
        }
        
        return $statusByCategory;
    }

    public static function getSpentPercentMonth($year, $month){
        
        $array = array();
        
        $data = self::select('category', DB::raw('sum(forecast) AS forecast'), DB::raw('sum(amount) AS amount'), DB::raw('(sum(amount)/sum(forecast))*100 AS percent'))->where('year', $year)->where('month', $month)->groupBy('category')->get();
        
        foreach($data AS $element){
            
            $array[$element->category] = [
                'detail' => $element,
                'color'  => self::getColor($element->percent)
                ];
        }
        
        return $array;
    }
 
    private static function getColor($percent) {

        $green = 0;
        $red = 0;

        $greenValue = max(0, 255 - min(255, ($percent / 100) * 255));
        $redValue = min(255, ($percent / 100) * 255);

        return "rgba($redValue, $greenValue, 0, 0.2)";
    }
 
    public static function getCurrentByCategories($year, $month) {
        
        $expenses_data = array();
        
        $expenses_categories = self::groupBy('category')->get();
        
        foreach($expenses_categories AS $category){
            $expenses_sub_categories = self::where('category', $category->category)->where('year', $year)->where('month', '<', $month+1)->get();
        
            foreach($expenses_sub_categories AS $sub_category){
                $expenses_data[$category->category][$sub_category->sub_category] = self::select( 'category', 'sub_category',DB::Raw('SUM(forecast) AS forecast'), DB::Raw('SUM(amount) AS amount'))->where('category', $sub_category->category)->where('sub_category', $sub_category->sub_category)->where('year', $year)->where('month', '<', $month+1)->first();
            }
        }
        
        return $expenses_data;
    } 
    
}
