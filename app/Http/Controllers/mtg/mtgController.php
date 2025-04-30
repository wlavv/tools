<?php

namespace App\Http\Controllers\mtg;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\mtg\mtg_sets;
use App\Models\mtg\mtg_cards;

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use Intervention\Image\ImageManagerStatic as Image;

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

        // Recupera a imagem base64
        $base64 = $request->input('base64_image');
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $binary = base64_decode($base64);

        if ($binary === false) {
            return response()->json(['error' => 'Imagem inválida'], 400);
        }

        // Salvar a imagem temporariamente
        $tempImagePath = storage_path('app/public/temp_image.jpg');
        file_put_contents($tempImagePath, $binary);

        // Gerar o pHash
        $imageHash = new ImageHash();
        echo $pHash = $imageHash->hash($tempImagePath);

        // Verificar se o pHash já existe no banco de dados
        $card = mtg_cards::where('hash', $pHash)->first();

        if ($card) {
            return response()->json(['found' => true, 'card' => $card, 'pHash' => $pHash]);
        } else {
            return response()->json(['found' => false, 'pHash' => $pHash]);
        }
    }


    public function compareHash(Request $request)
    {

        $base64Image = $request->input('image');
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

        $tempPath = storage_path('app/temp_capture.jpg');
        file_put_contents($tempPath, $imageData);

        // Inicializar o hasher com pHash
        $hasher = new ImageHash(new PerceptualHash());
        $capturedHash = $hasher->hash($tempPath);

        // Procurar cartas semelhantes
        $allCards = mtg_cards::all();

        $match = null;
        $lowestDistance = PHP_INT_MAX;

        foreach ($allCards as $card) {
            if (!$card->image_hash) continue;

            $distance = $capturedHash->distanceFrom($card->image_hash);

            if ($distance < $lowestDistance) {
                $lowestDistance = $distance;
                $match = $card;
            }
        }

        // Define um limite de distância aceitável para considerar "igual"
        if ($match && $lowestDistance <= 5) {
            return response()->json([
                'status' => 'success',
                'card' => $match,
                'distance' => $lowestDistance
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'distance' => $lowestDistance
        ]);

    }

    
}
