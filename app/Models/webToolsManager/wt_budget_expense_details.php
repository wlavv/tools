<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\webToolsManager\wt_budget_categories;

class wt_budget_expense_details extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'detail',
        'year',
        'month',
        'amount'
    ];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."budget_expense_details";
    }

    public static function getDetails($year, $month){
        
        $details = array();
        
        $details_categories = self::where('year', $year)->where('month', $month)->groupBy('slug')->get();
        
        foreach($details_categories AS $category){
            $details[$category->slug] = self::where('year', $year)->where('month', $month)->where('slug', $category->slug)->get();
        }
        
        return $details;
    }

    public static function addDataDetail($request){

        $insert = new wt_budget_expense_details();
        $insert->slug = $request->tag;
        $insert->detail = $request->detail;
        $insert->year = $request->year;
        $insert->month = $request->month;
        $insert->amount = $request->value;
        $insert->save();
        
        $value = self::where('year', $request->year)->where('month', $request->month)->where('slug', $request->tag)->sum('amount');
        
        wt_budget_expense::updateDataExpense($request, $value);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Orçamento atualizado com sucesso.'
        ]);
    }
    
    
    
    public static function updateDetail($request){
        
        $detail = wt_budget_expense_details::where('id', $request->id_detail)->first();
        $detail->amount = $request->value;
        $detail->detail = $request->detail;
        $detail->update();

        $total = wt_budget_expense_details::where('year', $detail->year)->where('month', $detail->month)->where('slug', $detail->slug)->sum('amount');

        wt_budget_expense::where('year', $detail->year)->where('month', $detail->month)->where('sub_category', $detail->slug)->update(['amount' => $total]);
        
        return 1;
    }
    
    
    
    public static function deleteDetail($request){

        $detail = wt_budget_expense_details::where('id', $request->id_detail)->first();
        
        $value = wt_budget_expense::where('year', $detail->year)->where('month', $detail->month)->where('sub_category', $detail->slug)->value('amount');

        $new_total = $value - $detail->amount;

        wt_budget_expense::where('year', $detail->year)->where('month', $detail->month)->where('sub_category', $detail->slug)->update(['amount' => $new_total]);

        $detail->delete();
        
        return 1;
    }
    
}
