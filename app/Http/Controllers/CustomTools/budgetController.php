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


use App\Models\webToolsManager\wt_budget_categories;

use Symfony\Component\DomCrawler\Crawler;

class budgetController extends customToolsController
{

    public function index(){
        
        $data = [
            'actions' => [],
            'budget'  => (object)self::createMonthlyBudget()
        ];
        
        $this->setViewData($data);
        return View::make('customTools/budget/index')->with( $this->viewData );
    }
    
    public static function createMonthlyBudget(){

        return [
            'income'  => self::getIncomeArray(),
            'expense' => self::getExpenseArray()
        ];

    }

    public static function getIncomeArray(){

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
    
    public static function getExpenseArray(){

        $structure = array();

        $expenseParents = wt_budget_categories::where('type', 'expense')->where('id_parent', 0)->get();

        foreach( $expenseParents AS $parent){

            $sons = wt_budget_categories::where('type', 'expense')->where('id_parent', $parent->id)->get();

            $sonsDetail = array();

            foreach($sons AS $son){
                $sonsDetail[ $son->slug ] = (object)[
                    'name' => $son->name,
                    'slug' => $son->slug,
                    'forecast' => $son->forecast
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
}
