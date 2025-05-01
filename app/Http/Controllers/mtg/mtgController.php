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

use Intervention\Image\Facades\Image;

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
        if (!$request->has('base64_image')) {
            return response()->json(['error' => 'Imagem não fornecida.'], 400);
        }
    
        $base64Image = $request->input('base64_image');
    
        // Limpar o prefixo base64 e decodificar
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
    
        if (!$imageData) {
            return response()->json(['error' => 'Imagem não foi decodificada corretamente.'], 400);
        }

        if (!preg_match('#^data:image/jpeg;base64,#', $base64Image)) {
            return response()->json(['error' => 'Formato de imagem não suportado (requer JPEG).'], 400);
        }

        // Criar imagem GD a partir dos dados
        $image = imagecreatefromstring($imageData);
    
        imagejpeg($image, public_path('debug.jpg')); // ou public_path()


        if (!$image) {
            return response()->json(['error' => 'Imagem inválida após conversão.'], 400);
        }
    
        // Verificar dimensões da imagem (evita pHash inválido)
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 32 || $height < 32) {
            return response()->json(['error' => 'Imagem demasiado pequena para gerar hash.'], 400);
        }
    
        try {
            $hasher = new \Jenssegers\ImageHash\ImageHash(new PerceptualHash());
            $hash = $hasher->hash($image);
    
            if (!$hash instanceof \Jenssegers\ImageHash\Hash) {
                return response()->json(['error' => 'Hash inválida (objeto não gerado)'], 500);
            }
    
            $inputHash = $hash->toHex();
    
            if (empty($inputHash) || $inputHash === str_repeat('0', strlen($inputHash))) {
                return response()->json(['error' => 'Hash não gerada ou inválida (zeros apenas)'], 400);
            }
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar o hash da imagem: ' . $e->getMessage()], 500);
        }
    
        // Lógica de comparação de hashes (deves garantir que é otimizada)
        $card = $this->compareHashes($inputHash);
    
        if ($card) {
            return response()->json([
                'message' => 'Carta encontrada!',
                'card' => [
                    'id' => $card->id,
                    'name' => $card->name,
                    'set_code' => $card->set_code,
                    'collector_number' => $card->collector_number,
                    'image_url' => $card->image_url,
                    'price' => $card->price,
                    'card_hash' => $card->hash,
                    'scan_hash' => $inputHash,
                ]
            ]);
        } else {
            return response()->json(['error' => 'Carta não encontrada.'], 404);
        }
    }
    
    
    public function hammingDistance($hash1, $hash2){

        if (strlen($hash1) !== strlen($hash2)) return false;

        $distance = 0;
        for ($i = 0; $i < strlen($hash1); $i++) {
            if ($hash1[$i] !== $hash2[$i]) $distance++;
        }

        return $distance;
    }

    public function compareHashes($inputHash){

        $userPrefix = substr($inputHash, 0, 8);

        $cards = mtg_cards::where('hash', 'LIKE', $userPrefix . '%')->get();

        foreach ($cards as $card) {
            $distance = $this->hammingDistance($inputHash, $card->hash);

            if ($distance < 10) return $card;
        }

        return null;
    }






















    
    public function processImage(Request $request){

        if (!$request->has('image')) return response()->json(['error' => 'Imagem não fornecida.'], 400);
    
        $base64Image = $request->input('image');
        $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
    
        $imageData = base64_decode($base64Image);
        $image = imagecreatefromstring($imageData);
    
        if (!$image) return response()->json(['error' => 'Imagem inválida.'], 400);
    
        $boundingBox = $request->input('boundingBox');
    
        if (!$boundingBox || !is_array($boundingBox) || count($boundingBox) != 4) return response()->json(['error' => 'Bounding box inválida.'], 400);
    
        list($x, $y, $width, $height) = $boundingBox;

        echo '<br>X: ' . $x;
        echo '<br>Y: ' . $y;
        echo '<br>WIDTH: ' . $width;
        echo '<br>HEIGHT: ' . $height;
        
        $croppedImage = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
    
        if (!$croppedImage) return response()->json(['error' => 'Falha ao cortar a imagem.'], 500);
    
        $filename = 'cropped_' . uniqid() . '.jpg';
        $path = public_path('uploads/mtg/front/temp/' . $filename);
        imagejpeg($croppedImage, $path);
    
        $imageHash = new ImageHash(new PerceptualHash());
        $hash = $imageHash->hash($croppedImage);
    
        $pHash = $hash->toHex();
    
        return response()->json([
            'pHash' => $pHash,
            'croppedImageUrl' => url("uploads/mtg/front/temp/{$filename}")
        ]);
    }
}