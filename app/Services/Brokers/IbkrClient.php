<?php

namespace App\Services\Brokers;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use App\Models\webToolsManager\wt_investments_brokerAccount;
use App\Models\Asset;
use App\Models\Position;

class IbkrClient
{

    protected CookieJar $cookieJar;

    public function __construct(
        protected ?wt_investments_brokerAccount $account = null
    ) {}

    public function withAccount(?wt_investments_brokerAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    protected function gatewayUrl(): string
    {
        return rtrim(config('services.ibkr.gateway_url', 'http://127.0.0.1:5000'), '/');
    }

    protected function timeout(): int
    {
        return (int) config('services.ibkr.timeout', 15);
    }

    /**
    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->gatewayUrl())
            ->timeout($this->timeout())
            ->acceptJson();
    }
    **/

    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->gatewayUrl())
            ->timeout($this->timeout())
            ->acceptJson()
            ->withOptions([
                'verify' => false, // DEV self-signed
                'cookies' => $this->cookieJar,
            ]);
    }

    /**
     * Teste de sessão/autenticação do Gateway.
     */
    public function authStatus(): array
    {
        return $this->http()
            ->get('/v1/api/iserver/auth/status')
            ->throw()
            ->json();
    }

    /**
     * Lista contas disponíveis no Gateway (IBKR).
     */
    public function portfolioAccounts(): array
    {
        return $this->http()
            ->get('/v1/api/portfolio/accounts')
            ->throw()
            ->json();
    }

    /**
     * Posições do accountId (ex.: U1234567).
     */
    public function positions(string $accountId): array
    {
        return $this->http()
            ->get("/v1/api/portfolio/{$accountId}/positions")
            ->throw()
            ->json();
    }

    /**
     * Sumário (saldo/valores). Endpoint pode variar conforme versão do Gateway.
     * Ajustamos se necessário com base na resposta real.
     */
    public function accountSummary(string $accountId): array
    {
        return $this->http()
            ->get("/v1/api/portfolio/{$accountId}/summary")
            ->throw()
            ->json();
    }

    /**
     * Devolve o account id associado no teu schema (external_account_id).
     */
    public function boundAccountId(): ?string
    {
        return $this->account?->external_account_id;
    }

    // Mantém os teus placeholders para fases futuras
    public function getLastPrice(Asset $asset): ?float
    {
        return null;
    }

    public function closePosition(Position $position, float $price): void
    {
        // TODO
    }
}
