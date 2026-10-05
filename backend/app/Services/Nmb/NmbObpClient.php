<?php

namespace App\Services\Nmb;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * NMB Bank Open Bank Project (OBP) client — agri-fintech core.
 *
 * Priority endpoints implemented:
 * 1. DirectLogin                          — auth
 * 2. GET  /banks, /banks/{BANK_ID}        — bank discovery
 * 3. GET  accounts / balances             — account information (AIS)
 * 4. GET  transactions                    — history for credit / reconciliation
 * 5. POST customers                       — farmer / FI onboarding
 * 6. POST counterparties                  — payout beneficiaries
 * 7. POST transaction-requests            — disbursements & seller payouts
 *
 * @see https://obp-apiexplorer-sandbox.nmbbank.co.tz/resource-docs/OBPv6.0.0
 * @see https://obp-api-sandbox.nmbbank.co.tz
 */
class NmbObpClient
{
    protected string $base;
    protected string $version;
    protected string $bankId;
    protected bool $mock;

    public function __construct()
    {
        $this->base = rtrim(config('nmb.base_url'), '/');
        $this->version = config('nmb.api_version', 'v5.0.0');
        $this->bankId = config('nmb.bank_id', 'nmbb.01.tz.nmbb');
        $this->mock = (bool) config('nmb.mock', true)
            || empty(config('nmb.consumer_key'))
            || empty(config('nmb.username'));
    }

    public function isMock(): bool
    {
        return $this->mock;
    }

    public function bankId(): string
    {
        return $this->bankId;
    }

    protected function path(string $suffix): string
    {
        return $this->base.'/obp/'.$this->version.'/'.ltrim($suffix, '/');
    }

    /**
     * DirectLogin → token (cached).
     * Header: DirectLogin username=..., password=..., consumer_key=...
     */
    public function directLogin(?string $username = null, ?string $password = null): array
    {
        if ($this->mock) {
            return [
                'token' => 'mock-nmb-token-'.Str::random(16),
                'mock' => true,
            ];
        }

        $username = $username ?: config('nmb.username');
        $password = $password ?: config('nmb.password');
        $key = config('nmb.consumer_key');

        $cacheKey = 'nmb_obp_token_'.md5($username.$key);
        if ($cached = Cache::get($cacheKey)) {
            return ['token' => $cached, 'mock' => false];
        }

        $header = sprintf(
            'username=%s, password=%s, consumer_key=%s',
            $username,
            $password,
            $key
        );

        $res = Http::timeout(config('nmb.timeout'))
            ->withHeaders([
                'DirectLogin' => $header,
                'Content-Type' => 'application/json',
            ])
            ->post($this->path('my/logins/direct'));

        if (!$res->successful()) {
            Log::warning('NMB DirectLogin failed', ['status' => $res->status(), 'body' => $res->body()]);
            throw new \RuntimeException('NMB DirectLogin failed: '.$res->body());
        }

        $token = $res->json('token') ?? $res->json()['token'] ?? null;
        if (!$token) {
            throw new \RuntimeException('NMB DirectLogin: token missing in response');
        }

        Cache::put($cacheKey, $token, now()->addMinutes(50));

        return ['token' => $token, 'mock' => false];
    }

    protected function authHeaders(): array
    {
        $login = $this->directLogin();

        return [
            'DirectLogin' => 'token='.$login['token'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    protected function get(string $suffix, array $query = []): array
    {
        if ($this->mock) {
            return $this->mockGet($suffix, $query);
        }

        $res = Http::timeout(config('nmb.timeout'))
            ->withHeaders($this->authHeaders())
            ->get($this->path($suffix), $query);

        if (!$res->successful()) {
            Log::warning('NMB OBP GET failed', ['path' => $suffix, 'status' => $res->status(), 'body' => $res->body()]);
            throw new \RuntimeException('NMB API error ['.$res->status().']: '.$res->body());
        }

        return $res->json() ?? [];
    }

    protected function post(string $suffix, array $body = []): array
    {
        if ($this->mock) {
            return $this->mockPost($suffix, $body);
        }

        $res = Http::timeout(config('nmb.timeout'))
            ->withHeaders($this->authHeaders())
            ->post($this->path($suffix), $body);

        if (!$res->successful()) {
            Log::warning('NMB OBP POST failed', ['path' => $suffix, 'status' => $res->status(), 'body' => $res->body()]);
            throw new \RuntimeException('NMB API error ['.$res->status().']: '.$res->body());
        }

        return $res->json() ?? [];
    }

    // ───────── Bank ─────────

    public function getBanks(): array
    {
        return $this->get('banks');
    }

    public function getBank(?string $bankId = null): array
    {
        $bankId = $bankId ?: $this->bankId;

        return $this->get('banks/'.$bankId);
    }

    // ───────── Accounts (AIS) ─────────

    public function getAccountsAtBank(?string $bankId = null): array
    {
        $bankId = $bankId ?: $this->bankId;

        return $this->get("banks/{$bankId}/accounts");
    }

    public function getAccount(string $accountId, ?string $bankId = null, ?string $viewId = null): array
    {
        $bankId = $bankId ?: $this->bankId;
        $viewId = $viewId ?: config('nmb.view_id', 'owner');

        return $this->get("banks/{$bankId}/accounts/{$accountId}/{$viewId}/account");
    }

    public function getAccountBalances(string $accountId, ?string $bankId = null): array
    {
        $bankId = $bankId ?: $this->bankId;

        return $this->get("banks/{$bankId}/accounts/{$accountId}/balances");
    }

    public function getAccountsBalances(?string $bankId = null): array
    {
        $bankId = $bankId ?: $this->bankId;

        return $this->get("banks/{$bankId}/balances");
    }

    // ───────── Transactions ─────────

    public function getTransactions(string $accountId, ?string $bankId = null, ?string $viewId = null): array
    {
        $bankId = $bankId ?: $this->bankId;
        $viewId = $viewId ?: config('nmb.view_id', 'owner');

        return $this->get("banks/{$bankId}/accounts/{$accountId}/{$viewId}/transactions");
    }

    // ───────── Customers ─────────

    public function createCustomer(array $data, ?string $bankId = null): array
    {
        $bankId = $bankId ?: $this->bankId;

        $payload = [
            'legal_name' => $data['legal_name'] ?? $data['name'] ?? 'Farmer',
            'mobile_phone_number' => $data['mobile_phone_number'] ?? $data['phone'] ?? '',
            'email' => $data['email'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? '1990-01-01',
            'relationship_status' => $data['relationship_status'] ?? 'Single',
            'dependants' => (int) ($data['dependants'] ?? 0),
            'dob_of_dependants' => $data['dob_of_dependants'] ?? [],
            'highest_education_attained' => $data['highest_education_attained'] ?? 'Bachelor',
            'employment_status' => $data['employment_status'] ?? 'Self-employed',
            'kyc_status' => $data['kyc_status'] ?? false,
            'last_ok_date' => now()->toIso8601String(),
            'title' => $data['title'] ?? '',
            'branch_id' => $data['branch_id'] ?? 'HQ',
            'name_suffix' => $data['name_suffix'] ?? '',
        ];

        return $this->post("banks/{$bankId}/customers", $payload);
    }

    public function getCustomers(?string $bankId = null): array
    {
        $bankId = $bankId ?: $this->bankId;

        return $this->get("banks/{$bankId}/customers");
    }

    // ───────── Counterparties ─────────

    public function createCounterparty(string $accountId, array $data, ?string $bankId = null, ?string $viewId = null): array
    {
        $bankId = $bankId ?: $this->bankId;
        $viewId = $viewId ?: config('nmb.view_id', 'owner');

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? $data['name'],
            'currency' => $data['currency'] ?? 'TZS',
            'other_account_routing_scheme' => $data['other_account_routing_scheme'] ?? 'accountNumber',
            'other_account_routing_address' => $data['other_account_routing_address'] ?? $data['account_number'],
            'other_account_secondary_routing_scheme' => $data['other_account_secondary_routing_scheme'] ?? 'accountNumber',
            'other_account_secondary_routing_address' => $data['other_account_secondary_routing_address'] ?? $data['account_number'],
            'other_bank_routing_scheme' => $data['other_bank_routing_scheme'] ?? 'bankCode',
            'other_bank_routing_address' => $data['other_bank_routing_address'] ?? 'NMB',
            'other_branch_routing_scheme' => $data['other_branch_routing_scheme'] ?? 'branchNumber',
            'other_branch_routing_address' => $data['other_branch_routing_address'] ?? '000',
            'is_beneficiary' => true,
            'bespoke' => $data['bespoke'] ?? [],
        ];

        return $this->post("banks/{$bankId}/accounts/{$accountId}/{$viewId}/counterparties", $payload);
    }

    // ───────── Payments (Transaction Requests) ─────────

    /**
     * Create payment / transfer via COUNTERPARTY (loan disbursement, seller payout).
     */
    public function createPaymentToCounterparty(
        string $accountId,
        string $counterpartyId,
        float $amount,
        string $currency = 'TZS',
        string $description = 'MkulimaHub transfer',
        ?string $bankId = null,
        ?string $viewId = null
    ): array {
        $bankId = $bankId ?: $this->bankId;
        $viewId = $viewId ?: config('nmb.view_id', 'owner');

        $body = [
            'to' => ['counterparty_id' => $counterpartyId],
            'value' => [
                'currency' => $currency,
                'amount' => number_format($amount, 2, '.', ''),
            ],
            'description' => $description,
            'charge_policy' => 'SHARED',
        ];

        return $this->post(
            "banks/{$bankId}/accounts/{$accountId}/{$viewId}/transaction-request-types/COUNTERPARTY/transaction-requests",
            $body
        );
    }

    /**
     * Sandbox ACCOUNT transfer (internal between sandbox accounts).
     */
    public function createPaymentToAccount(
        string $fromAccountId,
        string $toAccountId,
        float $amount,
        string $currency = 'TZS',
        string $description = 'MkulimaHub transfer',
        ?string $bankId = null,
        ?string $viewId = null
    ): array {
        $bankId = $bankId ?: $this->bankId;
        $viewId = $viewId ?: config('nmb.view_id', 'owner');

        $body = [
            'to' => [
                'bank_id' => $bankId,
                'account_id' => $toAccountId,
            ],
            'value' => [
                'currency' => $currency,
                'amount' => number_format($amount, 2, '.', ''),
            ],
            'description' => $description,
        ];

        return $this->post(
            "banks/{$bankId}/accounts/{$fromAccountId}/{$viewId}/transaction-request-types/ACCOUNT/transaction-requests",
            $body
        );
    }

    public function getTransactionRequestTypes(string $accountId, ?string $bankId = null, ?string $viewId = null): array
    {
        $bankId = $bankId ?: $this->bankId;
        $viewId = $viewId ?: config('nmb.view_id', 'owner');

        return $this->get("banks/{$bankId}/accounts/{$accountId}/{$viewId}/transaction-request-types");
    }

    public function status(): array
    {
        $hasKey = !empty(config('nmb.consumer_key'));
        $hasUser = !empty(config('nmb.username')) && !empty(config('nmb.password'));

        return [
            'provider' => 'NMB Bank (OBP)',
            'app_name' => config('nmb.app_name', 'Emish Store'),
            'base_url' => $this->base,
            'api_version' => $this->version,
            'bank_id' => $this->bankId,
            'consumer_id' => config('nmb.consumer_id') ?: null,
            'consumer_key_set' => $hasKey,
            'direct_login_ready' => $hasKey && $hasUser,
            'mock' => $this->mock,
            'escrow_account_id' => config('nmb.escrow_account_id'),
            'redirect_url' => config('nmb.redirect_url'),
            'docs' => 'https://obp-apiexplorer-sandbox.nmbbank.co.tz/resource-docs/OBPv6.0.0',
            'configured' => $hasKey,
            'note' => $hasKey && !$hasUser
                ? 'Consumer key present. Set NMB_OBP_USERNAME and NMB_OBP_PASSWORD for DirectLogin, or keep NMB_OBP_MOCK=true.'
                : ($this->mock ? 'Running in mock mode.' : 'Live OBP mode.'),
        ];
    }

    // ───────── Mock fixtures (local / no credentials) ─────────

    protected function mockGet(string $suffix, array $query = []): array
    {
        if ($suffix === 'banks') {
            return [
                'banks' => [[
                    'id' => $this->bankId,
                    'short_name' => 'NMB',
                    'full_name' => 'NMB Bank Plc (Sandbox)',
                    'logo' => '',
                    'website' => 'https://www.nmbbank.co.tz',
                ]],
            ];
        }

        if (str_starts_with($suffix, 'banks/') && substr_count($suffix, '/') === 1) {
            return [
                'id' => $this->bankId,
                'short_name' => 'NMB',
                'full_name' => 'NMB Bank Plc (Sandbox)',
                'bank_routings' => [['scheme' => 'OBP', 'address' => $this->bankId]],
            ];
        }

        if (str_contains($suffix, '/balances') && !str_contains($suffix, '/accounts/')) {
            return [
                'accounts' => [[
                    'account_id' => 'mkulima-escrow-001',
                    'bank_id' => $this->bankId,
                    'account_routings' => [['scheme' => 'accountNumber', 'address' => '0123456789']],
                    'balance' => ['currency' => 'TZS', 'amount' => '25000000.00'],
                ]],
            ];
        }

        if (str_contains($suffix, '/balances')) {
            return [
                'account_id' => 'mkulima-escrow-001',
                'bank_id' => $this->bankId,
                'balances' => [[
                    'type' => 'AVAILABLE',
                    'currency' => 'TZS',
                    'amount' => '1250000.00',
                ]],
            ];
        }

        if (str_contains($suffix, '/transactions')) {
            return [
                'transactions' => [
                    [
                        'id' => 'tx-mock-1',
                        'this_account' => ['id' => 'mkulima-escrow-001'],
                        'other_account' => ['id' => 'farmer-acc-1', 'holder' => ['name' => 'Aisha Juma']],
                        'details' => [
                            'type' => 'loan_disbursement',
                            'description' => 'Loan disbursement LA-DEMO',
                            'posted' => now()->subDays(2)->toIso8601String(),
                            'value' => ['currency' => 'TZS', 'amount' => '-2000000.00'],
                        ],
                    ],
                    [
                        'id' => 'tx-mock-2',
                        'this_account' => ['id' => 'mkulima-escrow-001'],
                        'other_account' => ['id' => 'buyer-acc-1', 'holder' => ['name' => 'Dar Fresh Traders']],
                        'details' => [
                            'type' => 'escrow_in',
                            'description' => 'Marketplace order payment',
                            'posted' => now()->subDay()->toIso8601String(),
                            'value' => ['currency' => 'TZS', 'amount' => '850000.00'],
                        ],
                    ],
                ],
            ];
        }

        if (str_ends_with($suffix, '/accounts') || str_contains($suffix, '/accounts')) {
            return [
                'accounts' => [[
                    'id' => 'mkulima-escrow-001',
                    'label' => 'MkulimaHub Escrow',
                    'bank_id' => $this->bankId,
                    'balance' => ['currency' => 'TZS', 'amount' => '1250000.00'],
                    'account_routings' => [['scheme' => 'accountNumber', 'address' => '0123456789']],
                ]],
            ];
        }

        if (str_contains($suffix, '/customers')) {
            return [
                'customers' => [[
                    'customer_id' => 'cust-mock-1',
                    'legal_name' => 'Demo Farmer',
                    'mobile_phone_number' => '0755123456',
                ]],
            ];
        }

        if (str_contains($suffix, 'transaction-request-types')) {
            return [
                'transaction_request_types' => [
                    ['value' => 'COUNTERPARTY', 'description' => 'Pay a counterparty'],
                    ['value' => 'ACCOUNT', 'description' => 'Transfer to another account'],
                    ['value' => 'SANDBOX_TAN', 'description' => 'Sandbox TAN payment'],
                ],
            ];
        }

        return ['mock' => true, 'path' => $suffix];
    }

    protected function mockPost(string $suffix, array $body): array
    {
        if (str_contains($suffix, '/customers')) {
            return [
                'customer_id' => 'cust-'.Str::random(8),
                'legal_name' => $body['legal_name'] ?? 'Farmer',
                'mobile_phone_number' => $body['mobile_phone_number'] ?? '',
                'bank_id' => $this->bankId,
                'mock' => true,
            ];
        }

        if (str_contains($suffix, '/counterparties')) {
            return [
                'id' => 'cp-'.Str::random(8),
                'name' => $body['name'] ?? 'Beneficiary',
                'other_account_routing_address' => $body['other_account_routing_address'] ?? '',
                'is_beneficiary' => true,
                'mock' => true,
            ];
        }

        if (str_contains($suffix, 'transaction-requests')) {
            return [
                'id' => 'tr-'.Str::random(10),
                'type' => str_contains($suffix, 'COUNTERPARTY') ? 'COUNTERPARTY' : 'ACCOUNT',
                'status' => 'COMPLETED',
                'value' => $body['value'] ?? ['currency' => 'TZS', 'amount' => '0'],
                'description' => $body['description'] ?? '',
                'mock' => true,
            ];
        }

        return ['mock' => true, 'path' => $suffix, 'echo' => $body];
    }
}
