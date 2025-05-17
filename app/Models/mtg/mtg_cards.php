<?php

namespace App\Models\mtg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use Intervention\Image\ImageManagerStatic as Image;

class mtg_cards extends Model
{   
    use HasFactory;

    protected $fillable = [
        'external_id',  // ID da API
        'collector_number',
        'hash',
        'name',         // Nome da carta
        'card_type',    // Tipo de carta (ex: Criatura, Feitiço, etc.)
        'color_group',
        'rarity',       // Raridade (comum, rara, mítica)
        'mana_cost',    // Custo de mana
        'power',        // Força (caso seja uma criatura)
        'toughness',    // Resistência (caso seja uma criatura)
        'oracle_text',  // Texto de sabor (se houver)
        'flavor_text',  // Texto de sabor (se houver)
        'image_url',    // URL da imagem
        'set_code',     // Código do set
        'set_name',     // Código do set
        'price',        // Preço da carta (se estiver sendo registrado)
        'scryfall_uri', // URL da carta na API do Scryfall
        'artist',       // URL da carta na API do Scryfall
        'link_gatherer',// URL da carta na API do Scryfall
        'link_edhrec',  // URL da carta na API do Scryfall
        'released_at',  // URL da carta na API do Scryfall
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $signature = 'scryfall:import-cards';
    protected $description = 'Importa todos os cards da API da Scryfall';

    public function __construct()
    {
        $this->table = "mtg_cards";
    }

    public static function checkIfExists($set_code, $collector_number){
        return self::where('set_code', $set_code)->where('collector_number', $collector_number)->exists();
    }

    public static function updateCardsFromSet($set_id, $set_code)
    {
        // A URL inicial para obter as cartas do set
        $url = 'https://api.scryfall.com/cards/search?q=set:' . $set_code . '&unique=prints';
    
        do {
            // Faz a requisição para obter as cartas
            $response = Http::get($url);
    
            if ($response->failed()) {
                return 1; // Se falhar a requisição, retorna 1
            }
    
            // Converte a resposta JSON
            $data = $response->json();
    
            // Itera sobre as cartas retornadas
            foreach ($data['data'] as $key => $card) {
    
                // Ignorar cartas digitais (Alchemy, Arena Only)
                if (isset($card['digital']) && $card['digital'] === true) {
                    //echo '<br>' . $set_code . ' | ' . $card['collector_number'] . ' | ' . $card['name'];
                    continue;
                }
                
                // Verifica se a carta já está na base de dados
                $exist_card = mtg_cards::checkIfExists($set_code, $card['collector_number']);
    
                if (!$exist_card) {
                    $filename = $card['collector_number'] . '.jpg';
    
                    $colorGroup = 0;
                    $colors = $card['colors'] ?? [];
                    if (count($colors) === 1) {
                        $colorGroup = $colors[0];
                    } elseif (count($colors) > 1) {
                        $colorGroup = 'M';
                    } else {
                        $colorGroup = 'C';
                    }
    
                    $path = 'images/mtg/' . $set_code;
                    $savePath = public_path( $path );
    
                    // Cria o diretório para as imagens
                    if (!file_exists($savePath)) mkdir($savePath, 0755, true);
    
                    $hasher = new ImageHash(new PerceptualHash()); // Instância do pHash

                    $imageContent = false;
                    $hash_image = '';
    
                    // Tenta baixar e salvar a imagem
                    if (isset($card['image_uris']['normal'])) {
                        $imageContent = file_get_contents($card['image_uris']['normal']);
                        file_put_contents($savePath . '/' . $filename, $imageContent);
                        //$hash_image = hash('sha256', $imageContent);
                        //$hash_image = (string) $hasher->hash($savePath . '/' . $filename);
                    } elseif (isset($card['card_faces'][0]['image_uris']['normal'])) {
                        $imageContent = file_get_contents($card['card_faces'][0]['image_uris']['normal']);
                        file_put_contents($savePath . '/' . $filename, $imageContent);
                        //$hash_image = hash('sha256', $imageContent);
                        //$hash_image = (string) $hasher->hash($savePath . '/' . $filename);
                    }

                    // Atualiza ou cria a carta no banco de dados
                    mtg_cards::updateOrCreate(
                        ['external_id' => $card['id']],
                        [
                            'collector_number' => $card['collector_number'],
                            'hash' => $hash_image,
                            'name' => $card['name'],
                            'card_type' => $card['type_line'] ?? null,
                            'rarity' => $card['rarity'] ?? null,
                            'color_group' => self::getColorIndex($colorGroup),
                            'mana_cost' => $card['mana_cost'] ?? null,
                            'power' => $card['power'] ?? null,
                            'toughness' => $card['toughness'] ?? null,
                            'oracle_text' => $card['oracle_text'] ?? null,
                            'flavor_text' => $card['flavor_text'] ?? null,
                            'image_url' => '/' . $path . '/' . $filename,
                            'set_code' => $set_code,
                            'set_name' => $card['set_name'] ?? null,
                            'set_id' => $set_id,
                            'price' => 0.05,
                            'scryfall_uri' => $card['scryfall_uri'] ?? null,
                            'artist' => $card['artist'] ?? null,
                            'link_gatherer' => $card['related_uris']['gatherer'] ?? null,
                            'link_edhrec' => $card['related_uris']['edhrec'] ?? null,
                            'released_at' => $card['released_at'] ?? null,
                        ]
                    );
                }
            }
    
            // Verifica se existe mais páginas e atualiza a URL
            if (isset($data['has_more']) && $data['has_more']) {
                $url = $data['next_page']; // Atualiza a URL para a próxima página
            } else {
                $url = null; // Se não houver mais páginas, encerra o loop
            }
    
        } while ($url); // Continua até que não haja mais páginas
    
        return 0;
    }

    
    public static function getColorIndex($colorGroup){

        $colorIndex = 0;

        switch ($colorGroup) {
            case 'W': $colorIndex= 1; break;
            case 'U': $colorIndex= 2; break;
            case 'B': $colorIndex= 3; break;
            case 'R': $colorIndex= 4; break;
            case 'G': $colorIndex= 5; break;
            case 'M': $colorIndex= 6; break;
            case 'C': $colorIndex= 7; break;
            default:  $colorIndex= 0; break;
        }

        return $colorIndex;      
    }
    
    public static function getCardsBySet($set_code){
        return self::where('set_code', $set_code)->orderBy('collector_number')->get();
    }
    
    public static function getCounters($set_code){

        return (object)[
            'all'           => self::where('set_code', $set_code)->count(),
            'white'         => self::where('set_code', $set_code)->where('color_group', 1)->count(),
            'blue'          => self::where('set_code', $set_code)->where('color_group', 2)->count(),
            'black'         => self::where('set_code', $set_code)->where('color_group', 3)->count(),
            'red'           => self::where('set_code', $set_code)->where('color_group', 4)->count(),
            'green'         => self::where('set_code', $set_code)->where('color_group', 5)->count(),
            'multicolor'    => self::where('set_code', $set_code)->where('color_group', 6)->count(),
            'colorless'     => self::where('set_code', $set_code)->where('color_group', 7)->count(),
        ];

    }
    
    public static function getCardCost($card){

        $html = $card->mana_cost;

        /**white**/ $html = str_replace('{W}', '<img style="width: 20px;margin: 2px;" src="/images/mtg/custom_images/W.svg" alt="Green mana">', $html);
        /**blue**/  $html = str_replace('{U}', '<img style="width: 20px;margin: 2px;" src="/images/mtg/custom_images/U.svg" alt="Blue mana">', $html);
        /**black**/ $html = str_replace('{B}', '<img style="width: 20px;margin: 2px;" src="/images/mtg/custom_images/B.svg" alt="Black mana">', $html);
        /**red**/   $html = str_replace('{R}', '<img style="width: 20px;margin: 2px;" src="/images/mtg/custom_images/R.svg" alt="Red mana">', $html);
        /**green**/ $html = str_replace('{G}', '<img style="width: 20px;margin: 2px;" src="/images/mtg/custom_images/G.svg" alt="Green mana">', $html);
        /**0**/     $html = str_replace('{0}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">0</div>', $html);
        /**0**/     $html = str_replace('{1}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">1</div>', $html);
        /**0**/     $html = str_replace('{2}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">2</div>', $html);
        /**0**/     $html = str_replace('{3}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">3</div>', $html);
        /**0**/     $html = str_replace('{4}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">4</div>', $html);
        /**0**/     $html = str_replace('{5}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">5</div>', $html);
        /**0**/     $html = str_replace('{6}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">6</div>', $html);
        /**0**/     $html = str_replace('{7}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">7</div>', $html);
        /**0**/     $html = str_replace('{8}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">8</div>', $html);
        /**0**/     $html = str_replace('{9}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">9</div>', $html);
        /**0**/     $html = str_replace('{10}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">10</div>', $html);
        /**0**/     $html = str_replace('{11}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">11</div>', $html);
        /**0**/     $html = str_replace('{12}', '<div style="width: 20px; height: 20px; border-radius: 50px; background: #ddd; float: left;line-height: 1;text-align: center;margin: 2px;">12</div>', $html);

        return $html;
    }
    
    public static function getCardColor($card){

        $color = 'UNKNOWN';

        switch ($card->color_group) {
            case 1: $color= 'WHITE'; break;
            case 2: $color= 'BLUE'; break;
            case 3: $color= 'BLACK'; break;
            case 4: $color= 'RED'; break;
            case 5: $color= 'GREEN'; break;
            case 6: $color= 'MULTI COLOR'; break;
            case 7: $color= 'COLORLESS'; break;
            default: $color= 'UNKNOWN';
        }

        return $color;

    }

}
