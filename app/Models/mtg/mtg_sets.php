<?php

namespace App\Models\mtg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class mtg_sets extends Model
{   
    use HasFactory;

    protected $fillable = [ 'external_id', 'set_code', 'sub_set_code', 'set_name', 'set_type', 'released_at', 'card_count', 'printed_size', 'digital', 'nonfoil_only', 'foil_only', 'icon_svg_uri', 'scryfall_uri', 'search_uri', 'json_uri' ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $signature = 'scryfall:import-sets';
    protected $description = 'Importa todos os sets da API da Scryfall';

    public function __construct()
    {
        $this->table = "mtg_sets";
    }

    public static function updateSets()
    {
        $url = 'https://api.scryfall.com/sets';

        do {
            $response = Http::get($url);
            if ($response->failed()) {
                return 1;
            }

            $data = $response->json();

            foreach ($data['data'] as $set) {

                $set_code = ( isset( $set['parent_set_code'] ) ) ? $set['parent_set_code'] : $set['code'];

                if($set_code == 'con') $set_code = $set_code.'s';
                $filename = $set_code . '.svg';

                $path = '/images/mtg/' . $set_code . '/logo';
                $savePath = public_path('/images/mtg/' . $set_code . '/logo');

                // Cria o diretório para as imagens
                if (!file_exists($savePath)) mkdir($savePath, 0755, true);

                $imageContent = false;
                $hash_image = '';

                $imageContent = file_get_contents( $set['icon_svg_uri'] );

                file_put_contents($savePath . '/' . $filename, $imageContent);

                mtg_sets::updateOrCreate(
                    ['external_id' => $set['id']],
                    [
                        'set_code' => ( isset( $set['parent_set_code'] ) ) ? $set['parent_set_code'] : $set['code'],
                        'sub_set_code' => $set['code'],
                        'set_name' => $set['name'],
                        'set_type' => $set['set_type'] ?? null,
                        'released_at' => $set['released_at'] ?? null,
                        'card_count' => $set['card_count'] ?? null,
                        'printed_size' => $set['printed_size'] ?? null,
                        'digital' => $set['digital'],
                        'foil_only' => $set['foil_only'],
                        'nonfoil_only' => $set['nonfoil_only'],
                        'icon_svg_uri' => $path . '/' . $filename,
                        'scryfall_uri' => $set['scryfall_uri'] ?? null,
                        'search_uri' => $set['search_uri'] ?? null,
                        'json_uri' => $set['uri'] ?? null,
                    ]
                );
            }

            $url = $data['has_more'] ? $data['next_page'] : null;

        } while ($url);

        echo 'Importação concluída com sucesso!';
        return 0;
    }

    public static function fetchCardsFromSet($setCode){

        $url = "https://api.scryfall.com/cards/search?q=e:$setCode";
        $allCards = [];


        do {
            $response = Http::get($url);
            
            if (!$response->successful()) {
                echo "Erro ao obter cartas do set $setCode | $url";
                break;
            }else{

                $data = $response->json();

                $allCards = array_merge($allCards, $data['data']);
                $url = $data['has_more'] ? $data['next_page'] : null;

                sleep(1); // respeitar a API rate limit
            }

        } while ($url);
         
        dd($allCards);
        return $allCards;
    }

    public static function countSubSet($set_code){
        return mtg_sets::where('set_code', $set_code)->count();
    }

    public static function getSet($set_code){
        return mtg_sets::where('sub_set_code', $set_code)->first();
    }

    public static function getByReleasedDate(){
        return mtg_sets::where('active', 1)->whereColumn('set_code', 'sub_set_code')->groupBy('set_code')->orderBy('released_at', 'DESC')->get();
    }

    public static function getByReleasedDateWithSetCode($set_code){
        return mtg_sets::where('active', 1)->where('set_code', $set_code)->orderBy('released_at', 'DESC')->get();
    }

}
