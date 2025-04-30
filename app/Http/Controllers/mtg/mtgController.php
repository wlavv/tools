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
        
    }public function findCardFromBase64(Request $request)
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
    
        $userHash = ImageHash::hash($binary); // Gera o pHash da imagem do usuário
        $userHashHex = $userHash->toHex(); // Converte o pHash para hexadecimal
    
        // Paginação: Limitar a busca a 100 cartas por vez para evitar carregar tudo na memória
        $perPage = 100;
        $page = 1; // Página inicial
        $foundMatch = false;
        $bestMatch = null;
        $lowestDistance = PHP_INT_MAX;
    
        do {
            // Filtra as cartas com base no set ou nome para reduzir o número de resultados
            $cards = mtg_cards::where('set_code', 'set_code_example') // Exemplo de filtro
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
    
            foreach ($cards as $card) {
                // Comparar o pHash gerado com o pHash da carta no banco de dados
                $distance = $this->hammingDistance($userHashHex, $card->hash); // Comparar diretamente os pHashes
    
                // Se encontrarmos uma carta mais próxima, atualizamos o melhor match
                if ($distance < $lowestDistance) {
                    $bestMatch = $card;
                    $lowestDistance = $distance;
                }
            }
    
            // Se já encontramos uma correspondência próxima o suficiente, podemos parar
            if ($lowestDistance <= 10) { // Limite de tolerância para considerar como correspondente
                $foundMatch = true;
                break;
            }
    
            // Avançar para a próxima página de resultados
            $page++;
    
        } while (count($cards) > 0); // Continuar até não haver mais cartas para comparar
    
        // Se encontramos uma correspondência
        if ($foundMatch) {
            return response()->json(['found' => true, 'card' => $bestMatch]);
        }
    
        return response()->json(['found' => false]);
    }
    
    // Função para calcular a distância Hamming entre dois hashes
    public function hammingDistance($hex1, $hex2)
    {
        $bin1 = hex2bin($hex1); // Converte o hash hexadecimal para binário
        $bin2 = hex2bin($hex2);
    
        // Calcula a distância Hamming
        $distance = 0;
        for ($i = 0; $i < strlen($bin1); $i++) {
            if ($bin1[$i] !== $bin2[$i]) {
                $distance++;
            }
        }
    
        return $distance;
    }
    

}
