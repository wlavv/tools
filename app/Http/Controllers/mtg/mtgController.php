<?php

namespace App\Http\Controllers\mtg;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\mtg\mtg_sets;
use App\Models\mtg\mtg_cards;

use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use Intervention\Image\ImageManagerStatic as Image;

use Illuminate\Support\Str;

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

        $data = [ 
            'breadcrumbs'=> $this->breadcrumbs,
            'sets' => mtg_sets::getByReleasedDate()
        ];

        return View::make('mtg/sets/index')->with($data);
    }

    public function showSet($set_code, $sub_set = 0)
    {
        $counter = mtg_sets::countSubSet($set_code);
        
        $set = mtg_sets::getSet($set_code);
        $this->breadcrumbs[] = [ 'name' =>  'mtg.showSet_info', 'params' => ['set' => $set->set_name], 'url' => route('mtg.showSet', $set_code)];

        if( ( $counter == 1 ) || ( $sub_set == 1 )  ){
            
            $data = [ 
                'breadcrumbs'=> $this->breadcrumbs,
                'set' => $set,
                'cards' => mtg_cards::getCardsBySet($set_code),
                'card_counters' => mtg_cards::getCounters($set_code)
            ];
    
            return View::make('mtg/cards/index')->with($data);

        }else{

            $sets = mtg_sets::getByReleasedDateWithSetCode($set_code);

            $data = [ 
                'breadcrumbs'=> $this->breadcrumbs,
                'sets' => $sets,
                'sub_set' => 0 
            ];
    
            return View::make('mtg/sets/index')->with($data);
        }
    }

    public function findCard()
    {
        
            $data = [ 
                'mtg_icon' => '/images/mtg/mana/mtg.png',
            ];
    
            return View::make('mtg/front/find')->with($data);
        
    }
    
    public function findCardFromBase64(Request $request)
    {
        $request->validate([
            'base64_image' => 'required|string',
        ]);
    
        $base64 = $request->input('base64_image');
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $binary = base64_decode($base64);
    
        if ($binary === false) {
            return response()->json(['error' => 'Imagem inválida'], 400);
        }
    
        // Gera o pHash da imagem do usuário
        $imageHash = new \Jenssegers\ImageHash\ImageHash();
        $userHash = $imageHash->hash($binary);
        $userHashHex = $userHash->toHex();
        $userPrefix = substr($userHashHex, 0, 8); // Pegue os primeiros 8 caracteres
    
        // Buscar cartas no banco com base no prefixo usando LIKE
        $cards = mtg_cards::where('hash', 'LIKE', $userPrefix . '%')->get();
    
        $foundMatch = false;
        $bestMatch = null;
        $lowestDistance = PHP_INT_MAX;
    
        foreach ($cards as $card) {
            $distance = $this->hammingDistance($userHashHex, $card->hash);
    
            if ($distance < $lowestDistance) {
                $bestMatch = $card;
                $lowestDistance = $distance;
            }
        }
    
        // Se encontramos uma correspondência
        if ($lowestDistance <= 10) { // Se a distância for pequena o suficiente
            $foundMatch = true;
        }
    
        // Retorna a resposta
        return response()->json(['found' => $foundMatch, 'card' => $bestMatch]);
    }
    
}
