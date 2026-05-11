<?php

namespace Modules\Investments\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\Investments\Models\Asset;
use Modules\Investments\Models\BrokerAccount;
use Modules\Investments\Models\Position;

class IbkrClient
{
    protected CookieJar $cookieJar;

    public function __construct(protected ?BrokerAccount $account = null)
    {
        $this->cookieJar = new CookieJar();
    }

    public function withAccount(?BrokerAccount $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function authStatus(): array
    {
        return $this->http()->get('/v1/api/iserver/auth/status')->throw()->json();
    }

    public function portfolioAccounts(): array
    {
        return $this->http()->get('/v1/api/portfolio/accounts')->throw()->json();
    }

    public function positions(string $accountId): array
    {
        return $this->http()->get("/v1/api/portfolio/{$accountId}/positions")->throw()->json();
    }

    public function accountSummary(string $accountId): array
    {
        return $this->http()->get("/v1/api/portfolio/{$accountId}/summary")->throw()->json();
    }

    public function boundAccountId(): ?string
    {
        return $this->account?->external_account_id;
    }

    public function getLastPrice(Asset $asset): ?float
    {
        return null;
    }

    public function closePosition(Position $position, float $price): void
    {
        // Broker order execution will be added when live trading is enabled.
    }

    protected function gatewayUrl(): string
    {
        return rtrim(config('services.ibkr.gateway_url', 'http://127.0.0.1:5000'), '/');
    }

    protected function timeout(): int
    {
        return (int) config('services.ibkr.timeout', 15);
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->gatewayUrl())
            ->timeout($this->timeout())
            ->acceptJson()
            ->withOptions([
                'verify' => false,
                'cookies' => $this->cookieJar,
            ]);
    }
}
