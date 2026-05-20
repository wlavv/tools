<?php

namespace Modules\Mtg\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Mtg\Models\mtg_cards;
use Modules\Mtg\Models\mtg_sets;
use Modules\WebCatalogue\Models\Store;

class MtgController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        $sets = mtg_sets::getByReleasedDate();

        if (config('mtg.sync_sets_on_index', true)) {
            $counter = 0;
            $limit = (int) config('mtg.sync_sets_limit', 10);

            foreach ($sets as $set) {
                mtg_cards::updateCardsFromSet($set->id, $set->set_code);
                $counter += 1;

                if ($counter > $limit) {
                    break;
                }
            }
        }

        return $this->view('mtg::Index', [
            'sets' => mtg_sets::getByReleasedDate(),
        ]);
    }

    public function showSet(string $code, int|string $sub_set = 0): View
    {
        $counter = mtg_sets::countSubSet($code);
        $set = mtg_sets::getSet($code);

        $this->setPageTitle($set->set_name ?? 'MTG Set');

        if (($counter == 1) || ((int) $sub_set === 1)) {
            return $this->view('mtg::cards.index', [
                'set' => $set,
                'cards' => mtg_cards::getCardsBySet($code),
                'card_counters' => mtg_cards::getCounters($code),
                'webcatalogueStores' => Store::orderBy('name')->get(),
            ]);
        }

        return $this->view('mtg::Index', [
            'sets' => mtg_sets::getByReleasedDateWithSetCode($code),
            'sub_set' => 0,
        ]);
    }

    public function findCard(): View
    {
        $this->hideBreadcrumbs();

        return $this->view('mtg::front.find', [
            'mtg_icon' => '/images/mtg/mana/mtg.png',
        ]);
    }

    public function postCardDetail(Request $request): View
    {
        $card = mtg_cards::where('set_code', $request->input('edition'))
            ->where('collector_number', $request->input('collector_number'))
            ->firstOrFail();

        return view('mtg::front.includes.AR_content', [
            'card' => $card,
            'card_cost' => mtg_cards::getCardCost($card),
            'card_color' => mtg_cards::getCardColor($card),
        ]);
    }

    public function generateDescription(int $id): string
    {
        $card = mtg_cards::where('id', $id)->first();

        if (!isset($card)) {
            abort(404);
        }

        echo $card->name;

        $encodedName = urlencode($card->name);
        $url = 'https://api.scryfall.com/cards/named?exact=' . $encodedName;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: MyMTGApp/1.0\r\n",
            ],
        ]);

        $cardJson = file_get_contents($url, false, $context);

        if ($cardJson === false) {
            die('Erro ao aceder a API da Scryfall.');
        }

        $cardData = json_decode($cardJson, true);
        $description = $cardData ? self::gerarDescricaoCartaMTG($cardData) : '';

        return $description;
    }

    public static function extractAbilities(array $cardData): array
    {
        $abilities = [];

        if (isset($cardData['keywords'])) {
            $abilities = array_merge($abilities, $cardData['keywords']);
        }

        if (isset($cardData['oracle_text'])) {
            $oracle = $cardData['oracle_text'];

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

    public static function gerarDescricaoCartaMTG(array $cardData): string
    {
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

        $habilidades = !empty($palavrasChave) ? implode(', ', $palavrasChave) : '';
        $textoRegras = !empty($oracle) ? str_replace("\n", ' ', $oracle) : '';
        $cor = 'incolor';

        if (!empty($cores)) {
            $cor = implode(', ', array_map(function ($c) {
                return match ($c) {
                    'W' => 'branca',
                    'U' => 'azul',
                    'B' => 'preta',
                    'R' => 'vermelha',
                    'G' => 'verde',
                    default => $c,
                };
            }, $cores));
        }

        $descricao = "<strong>{$nome}</strong> e uma carta {$cor} do tipo <em>{$tipo}</em>";

        if ($manaCost) {
            $descricao .= ", com um custo de mana de {$manaCost}";
        }

        $descricao .= '. ';

        if ($poderResistencia) {
            $descricao .= "Apresenta estatisticas de combate com {$poderResistencia} de poder e resistencia. ";
        }

        if ($habilidades) {
            $descricao .= "Destaca-se pelas seguintes habilidades: <em>{$habilidades}</em>. ";
        }

        if ($textoRegras) {
            $descricao .= "De acordo com o texto da carta, \"{$textoRegras}\" ";
        }

        $descricao .= "Esta versao faz parte da colecao <strong>{$colecao}</strong>, sendo o numero {$numero} do conjunto e classificada como uma carta de raridade <strong>{$raridade}</strong>. ";
        $descricao .= "A ilustracao e da autoria de <strong>{$ilustrador}</strong>, trazendo ainda mais vida e identidade a esta carta.";

        return nl2br(trim($descricao));
    }
}
