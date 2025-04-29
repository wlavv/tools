<?php

namespace App\Http\Controllers\mtg;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\mtg\mtg_sets;
use App\Models\mtg\mtg_cards;

class mtgController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'Magic the Gathering', 'url' => route('mtg.index')];
    }

    public function index()
    {
        /** PROCURA POR NOVOS SETS E ATUALIZA OS ACTUAIS **/
        //mtg_sets::updateSets();

        /** PROCURA POR NOVAS CARTAS E ATUALIZA OS ACTUAIS **/
        /**
        $counter = 0;
        $sets = mtg_sets::getByReleasedDate();

        foreach($sets AS $set){
            mtg_cards::updateCardsFromSet($set->id, $set->set_code);

            $counter +=1;

            if($counter > 10) break;
        }
        **/

        $counter = 0;
        $sets = mtg_sets::getByReleasedDate();

        foreach($sets AS $set){
            mtg_cards::updateCardsFromSet($set->id, $set->set_code);

            $counter +=1;

            if($counter > 10) break;
        }

        $data = [ 
            'breadcrumbs'=> $this->breadcrumbs,
            'sets' => mtg_sets::getByReleasedDate() 
        ];

        return View::make('mtg/sets/index')->with($data);
    }

    public function showSet($set_code)
    {

        $set = mtg_sets::getSet($set_code);

        $this->breadcrumbs[] = [ 'name' =>  'mtg.showSet_info', 'params' => ['set' => $set->set_name], 'url' => route('mtg.showSet', $set_code)];

        $data = [ 
            'breadcrumbs'=> $this->breadcrumbs,
            'set' => $set,
            'cards' => mtg_cards::getCardsBySet($set_code) 
        ];

        return View::make('mtg/cards/index')->with($data);
    }

    
}
