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
        $imageBase64 = $request->input('base64_image');
    
        if (!$imageBase64) {
            return response()->json(['error' => 'Imagem não fornecida.'], 400);
        }
    
        // Extrai e converte a imagem
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));
        $tempFile = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempFile, $imageData);
    
        // Gera o pHash da imagem
        $hasher = new ImageHash(new PerceptualHash());
        $userHash = $hasher->hash($tempFile)->toHex(); // Ex: "94796b677ade68cf"
        unlink($tempFile); // Remove o ficheiro temporário
    
        // Define prefixo para pré-filtragem (p.ex. 4 hex digits = 16 bits)
        $prefix = substr($userHash, 0, 4);
        $maxDistance = 12; // Tolerância ajustável
        $bestMatch = null;
        $bestDistance = 999;
    
        // Busca candidatos com prefixo semelhante
        $candidates = mtg_cards::where('hash', 'like', $prefix . '%')->limit(200)->get();
    
        foreach ($candidates as $card) {

            $distance = $hasher->distance(
                Hash::fromHex($userHash),
                Hash::fromHex($card->hash)
            );
            
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $card;
            }
    
            if ($bestDistance <= $maxDistance) break; // Match aceitável encontrado
        }
    
        if ($bestMatch && $bestDistance <= $maxDistance) {
            return response()->json([
                'pHash' => $userHash,
                'card_name' => $bestMatch->name,
                'set_code' => $bestMatch->set_code,
                'collector_number' => $bestMatch->collector_number,
                'distance' => $bestDistance,
                'image_url' => $bestMatch->image_url
            ]);
        }
    
        return response()->json([
            'pHash' => $userHash,
            'message' => 'Nenhuma carta compatível encontrada',
            'distance' => $bestDistance
        ], 404);
    }

}
