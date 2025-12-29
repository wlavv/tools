<?php

namespace App\Services\Brokers;

use Illuminate\Support\Facades\Http;
use App\Models\BrokerAccount;
use App\Models\Asset;
use App\Models\Position;

class IbkrClient
{
    public function __construct(
        protected ?BrokerAccount $account = null
    ) {}

    protected function baseUrl(): string
    {
        return config('services.ibkr.base_url', 'https://localhost:5000/v1/api');
    }

    protected function http()
    {
        // Aqui no futuro podes gerir sessão e headers específicos
        return Http::baseUrl($this->baseUrl())
            ->acceptJson();
    }

    public function getLastPrice(Asset $asset): ?float
    {
        // TODO: substituir pelo endpoint real da IBKR
        // $res = $this->http()->get("/marketdata/{$asset->external_instrument_id}/last");
        // return $res->json('price');

        return null; // placeholder
    }

    public function closePosition(Position $position, float $price): void
    {
        // TODO: enviar ordem de fecho para IBKR e atualizar a posição
    }
}
