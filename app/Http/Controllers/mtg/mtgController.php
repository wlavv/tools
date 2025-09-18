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
        
        //mtg_cards::updateCardsFromSet(836, 'mrd');

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

    public function postCardDetail(Request $request){
        
        $card = mtg_cards::where('set_code', $request->input('edition'))->where('collector_number', $request->input('collector_number'))->firstOrFail();
        $card_cost = mtg_cards::getCardCost($card);
        $card_color = mtg_cards::getCardColor($card);
        
        return view('mtg.front.includes.AR_content', compact('card', 'card_cost', 'card_color'));

    }

    public function generateDescription($id){
        
        $card = mtg_cards::where('id', $id)->first();
        
        if(isset($card)){
            
            echo $card->name;

            $encodedName = urlencode($card->name);
            $url = "https://api.scryfall.com/cards/named?exact=" . $encodedName;
            
            $options = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: MyMTGApp/1.0\r\n"
                ]
            ];
            $context = stream_context_create($options);
            
            $cardJson = file_get_contents($url, false, $context);
            
            if ($cardJson === false) {
                die("Erro ao aceder à API da Scryfall.");
            }
            
            $cardData = json_decode($cardJson, true);
            
            $description = '';
            if ($cardData) {
                $description = self::gerarDescricaoCartaMTG($cardData);
            }

            
            dd($description);
        }

    }


    public static function extractAbilities($cardData) {
        $abilities = [];
    
        // 1. Palavras-chave
        if (isset($cardData['keywords'])) {
            $abilities = array_merge($abilities, $cardData['keywords']);
        }
    
        // 2. oracle_text (habilidades adicionais)
        if (isset($cardData['oracle_text'])) {
            $oracle = $cardData['oracle_text'];
    
            // Expressões simples para identificar padrões
            if (preg_match_all('/Whenever .*?,.*?\./', $oracle, $matches)) {
                $abilities = array_merge($abilities, $matches[0]);
            }
    
            if (preg_match_all('/\{.*?\}:.*?\./', $oracle, $matches)) {
                $abilities = array_merge($abilities, $matches[0]);
            }
    
            if (preg_match_all('/At the beginning of .*?,.*?\./', $oracle, $matches)) {
                $abilities = array_merge($abilities, $matches[0]);
            }
        }
    
        return $abilities;
    }   
    
    public static function gerarDescricaoCartaMTG(array $cardData): string {
        $nome = $cardData['name'] ?? 'Carta desconhecida';
        $tipo = $cardData['type_line'] ?? '';
        $manaCost = $cardData['mana_cost'] ?? '';
        $raridade = ucfirst($cardData['rarity'] ?? '');
        $colecao = $cardData['set_name'] ?? '';
        $numero = $cardData['collector_number'] ?? '';
        $ilustrador = $cardData['artist'] ?? '';
        $oracle = $cardData['oracle_text'] ?? '';
        $palavrasChave = $cardData['keywords'] ?? [];
        $cores = $cardData['colors'] ?? [];
    
        $poderResistencia = '';
        if (!empty($cardData['power']) && !empty($cardData['toughness'])) {
            $poderResistencia = "{$cardData['power']}/{$cardData['toughness']}";
        }
    
        // Habilidades
        $habilidades = '';
        if (!empty($palavrasChave)) {
            $habilidades = implode(', ', $palavrasChave);
        }
    
        // Texto de regras formatado
        $textoRegras = '';
        if (!empty($oracle)) {
            $textoRegras = str_replace("\n", ' ', $oracle);
        }
    
        // Cor
        $cor = 'incolor';
        if (!empty($cores)) {
            $cor = implode(', ', array_map(function($c) {
                switch ($c) {
                    case 'W': return 'branca';
                    case 'U': return 'azul';
                    case 'B': return 'preta';
                    case 'R': return 'vermelha';
                    case 'G': return 'verde';
                    default: return $c;
                }
            }, $cores));
        }
    
        // Início da descrição
        $descricao = "<strong>{$nome}</strong> é uma carta {$cor} do tipo <em>{$tipo}</em>";
        if ($manaCost) {
            $descricao .= ", com um custo de mana de {$manaCost}";
        }
        $descricao .= ". ";
    
        if ($poderResistencia) {
            $descricao .= "Apresenta estatísticas de combate com {$poderResistencia} de poder e resistência. ";
        }
    
        if ($habilidades) {
            $descricao .= "Destaca-se pelas seguintes habilidades: <em>{$habilidades}</em>. ";
        }
    
        if ($textoRegras) {
            $descricao .= "De acordo com o texto da carta, \"{$textoRegras}\" ";
        }
    
        $descricao .= "Esta versão faz parte da coleção <strong>{$colecao}</strong>, sendo o número {$numero} do conjunto e classificada como uma carta de raridade <strong>{$raridade}</strong>. ";
        $descricao .= "A ilustração é da autoria de <strong>{$ilustrador}</strong>, trazendo ainda mais vida e identidade a esta carta.";
    
        return nl2br(trim($descricao)); // Opcional para HTML
    }


}