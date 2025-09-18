<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\webToolsManager\wt_customer;
use App\Models\webToolsManager\wt_orders;
use App\Models\webToolsManager\wt_product;
use App\Models\webToolsManager\wt_orders_details;


use App\Models\webToolsManager\wt_budget_income;
use App\Models\webToolsManager\wt_budget_expense;
use App\Models\webToolsManager\wt_budget_categories;
use App\Models\webToolsManager\wt_budget_expense_details;
use App\Models\webToolsManager\wt_budget_objectives;

use Symfony\Component\DomCrawler\Crawler;

class budgetController extends customToolsController
{
    private static $forecast =  2216.16;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'Budget', 'url' => route('budget.index')];
    }
    
    public function index(){
        
        $month = ( isset( $_GET['month'])) ? $_GET['month'] : date('m');
        $year  = ( isset( $_GET['year']))  ? $_GET['year']  : date('Y');

        /*$expenses =  wt_budget_expense::where('year', $year)->where('month', $month)->whereNotIn('category', ['potes'])->get();
        dd($expenses);*/
        
        $total_income = wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary', 'marcia_salary', 'meninas_income', 'extra_income'])->sum('amount');
        $expenses =  wt_budget_expense::where('year', $year)->where('month', $month)->whereNotIn('category', ['potes'])->sum('amount');
        $potes = wt_budget_expense::where('year', $year)->where('month', $month)->where('category', 'potes')->sum('amount');
        self::$forecast = wt_budget_categories::where('forecast_year', $year)->where('type', 'expense')->where('id_parent', '>', 0)->where('id_parent', '<>', 44)->sum('forecast');
        
        $already_spent_percent = wt_budget_expense::getSpentPercentMonth($year, $month);
        
        $data = [
            'actions' => [],
            'year' => $year,
            'month' => $month,
            'total_income' => $total_income,
            'bruno_salary' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary'])->sum('amount'),
            'bruno_asm' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_asm'])->sum('amount'),
            'bruno_cv' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_cv'])->sum('amount'),
            'bruno_income' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['bruno_salary', 'bruno_asm', 'bruno_cv'])->sum('amount'),
            'marcia_salary' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_salary'])->sum('amount'),
            'marcia_oriflame' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_oriflame'])->sum('amount'),
            'marcia_income' => wt_budget_income::where('year', $year)->where('month', $month)->whereIn('category', ['marcia_salary', 'marcia_oriflame'])->sum('amount'),
            'extra_income' => wt_budget_income::where('year', $year)->where('month', $month)->where('category', 'extra_income')->sum('amount'),
            'meninas_income' => wt_budget_income::where('year', $year)->where('month', $month)->where('category', 'meninas_income')->sum('amount'),
            'potes' => $potes,
            'forecast' => self::$forecast,
            'expenses' => $expenses,
            'differences' => ( self::$forecast - $expenses ),
            'budget_percentage' => ( 1 - ( $expenses / self::$forecast ))*100,
            'budget_max' => -( ( self::$forecast * 1.1 ) - $expenses ),
            'savings' => $total_income-$expenses-700,
            'budget'  => (object)self::createMonthlyBudget($year, $month),
            'status_year'  => wt_budget_expense::getStatus($year, $month),
            'details' => wt_budget_expense_details::getDetails($year, $month),
            'isMobile' => self::isMobile(),
            'spent' => $already_spent_percent,
            'objectives_short' => wt_budget_objectives::getObjectives(1, $year, $month),
            'objectives_medium' => wt_budget_objectives::getObjectives(2, $year, $month),
            'objectives_long' => wt_budget_objectives::getObjectives(3, $year, $month),
        ];
        
        $this->setViewData($data);
        
        return View::make('customTools/budget/index')->with( $this->viewData );
    }

    function isMobile() {
        return preg_match('/Mobile|Android|iP(hone|od|ad)|IEMobile|BlackBerry|Kindle|Opera Mini|webOS/', request()->header('User-Agent'));
    }

    public static function updateData(Request $request){
        
        $data = null;
        if( $request->type == 'income' ) $data = wt_budget_income::updateDataIncome($request);
        if( $request->type == 'expense' ) $data= wt_budget_expense::updateDataExpense($request);
        if( $request->type == 'addDetail' ) $data= wt_budget_expense_details::addDataDetail($request);
            
        return $data;
    }
    
    public static function updateForecastData(Request $request){
        
        $year = $request->year;
        $month = $request->month;
        
        wt_budget_expense::where('year', $year)->where('month', $month)->where('sub_category', $request->tag)->update(['forecast' => $request->value]);
        wt_budget_categories::where('forecast_year', $year)->where('slug', $request->tag)->update(['forecast' => $request->value]);
            

        return response()->json([
            'status' => 'success',
            'message' => 'Forecast atualizado com sucesso.'
        ]);
        
    }
    
    public static function createMonthlyBudget($year, $month){
        
        return [
            'income'  => self::getIncomeArray($year, $month),
            'expense' => self::getExpenseArray($year, $month)
        ];

    }

    public static function getIncomeArray($year, $month){

        $structure = array();

        $incomeParents = wt_budget_categories::where('type', 'income')->where('id_parent', 0)->get();

        foreach( $incomeParents AS $parent){

            $sons = wt_budget_categories::where('type', 'income')->where('id_parent', $parent->id)->get();

            $sonsDetail = array();

            foreach($sons AS $son){
                $sonsDetail[ $son->slug ] = [
                    'name' => $son->name,
                    'slug' => $son->slug,
                    'forecast' => $son->forecast
                ];
            }

            $structure[ ]  = [
                'name' => $parent->name,
                'slug' => $parent->slug,
                'forecast' => $parent->forecast,
                'sons' => $sonsDetail
            ];

        }

        return $structure;
    }
    
    public static function getExpenseArray($year, $month){

        $structure = array();

        $expenseParents = wt_budget_categories::where('type', 'expense')->where('id_parent', 0)->get();

        foreach( $expenseParents AS $parent){

            $sons = wt_budget_categories::select('wt_budget_categories.name', 'wt_budget_categories.slug', 'wt_budget_categories.forecast', 'wt_budget_expense.amount')->leftJoin('wt_budget_expense', function($join) use ($year, $month) {
                $join->on('wt_budget_expense.sub_category', '=', 'wt_budget_categories.slug')
                     ->where('wt_budget_expense.year', $year)
                     ->where('wt_budget_expense.month', $month);
                })
                ->where('wt_budget_categories.type', 'expense')
                ->where('wt_budget_categories.id_parent', $parent->id)
                ->orderBy('wt_budget_categories.id')
                ->get();

            $sonsDetail = array();

            foreach($sons AS $son){
                $sonsDetail[ $son->slug ] = (object)[
                    'name' => $son->name,
                    'slug' => $son->slug,
                    'forecast' => $son->forecast,
                    'expense' => $son->amount
                ];
            }

            $structure[ $parent->slug ]  = (object)[
                'name' => $parent->name,
                'slug' => $parent->slug,
                'forecast' => $parent->forecast,
                'sons' => (object)$sonsDetail
            ];

        }
        
        return (object)$structure;
    }
    
    public static function updateDetail(Request $request){
    
        wt_budget_expense_details::updateDetail($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Row updated!'
        ]);
    }
    
    public static function deleteDetail(Request $request){
        
        wt_budget_expense_details::deleteDetail($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Row deleted!'
        ]);
        
    }
    
    public static function addObjective(Request $request){
        return wt_budget_objectives::createRow($request);
    }
    
    public static function setObjectiveAsDone(Request $request){
        return wt_budget_objectives::done($request);
    }
    
}
