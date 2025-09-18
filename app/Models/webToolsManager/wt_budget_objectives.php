<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\webToolsManager\wt_budget_categories;

class wt_budget_objectives extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'amount',
        'priority',
        'category',
        'sub_category',
        'type',
        'link'
    ];

    public function __construct(){
        $this->table = env('DB2_prefix')."budget_objectives";
    }
    
    public static function createRow($request){

        $category = explode('|', $request->objective_income_source);

        $data = [
            'name'         => $request->objective_name,
            'amount'       => $request->objective_value,
            'priority'     => $request->objective_priority,
            'category'     => $category[0],
            'sub_category' => $category[1] ?? '',
            'type'         => $request->objective_type,
            'link'         => $request->objective_link,
        ];
    
        $objective = self::updateOrCreate(
            ['id' => $request->objective_id ?? null],
            $data                           
        );
    
        return response()->json([
            'status' => 'success',
            'message' => 'Objective inserted!'
        ]);
        
    }   
    
    public static function getObjectives($type, $year, $month){
        
        $objectives_data = self::where('type', $type)->where('done', 0)->orderBy('priority')->get();
        
        $expenses = wt_budget_expense::getCurrentByCategories($year, $month);

        foreach($objectives_data AS $data){
            
            if( isset($expenses[$data->category]) && isset($expenses[$data->category][$data->sub_category]) ){
    
                $available_amount = ( $expenses[$data->category][$data->sub_category]->forecast - $expenses[$data->category][$data->sub_category]->amount );
                
                if( $available_amount > 0){
                    
                    if( $available_amount > $data->amount){

                        $data->available = '100%';

                        $expenses[$data->category][$data->sub_category]->amount += $data->amount;
                        
                        $data->buy = 1;
                    }else{
                        
                        $value_available = $expenses[$data->category][$data->sub_category]->forecast - $expenses[$data->category][$data->sub_category]->amount;
                        
                        $data->available = number_format(( $value_available / $data->amount ) * 100, 2, '.', ' ') . ' % <br><span style="color: green;">( ' . number_format($value_available, 2, '.', ' ') . ' € )</span>';
                        $expenses[$data->category][$data->sub_category]->amount += $data->amount;
                        $data->buy = 0;
                    }
                }
            }

        }
        
        return $objectives_data;
    }   

    public static function done($request){
        self::where('id', $request->id)->update(['done' => 1]);
    }
    
}