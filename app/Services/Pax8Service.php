<?php

namespace App\Services;

use App\Models\Pax8Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Pax8Service
{
    private const BASE_URL = 'https://api.pax8.com/v1';
    private const TOKEN_URL = 'https://api.pax8.com/v1/token';
    private const AUDIENCE = 'https://api.pax8.com';

    /**
     * 🔐 Get Pax8 access token (cached ~23.5 hours)
     */
    public function getAccessToken(): ?string
    {
        // Check if we already have a valid cached token
        $cachedToken = Cache::get('pax8_access_token');
        if ($cachedToken) {
            Log::debug('♻️ Using cached Pax8 token');
            return $cachedToken;
        }

        // No cached token, fetch a new one
        try {
            $payload = [
                'client_id' => config('app.pax8.client_id'),
                'client_secret' => config('app.pax8.client_secret'),
                'grant_type' => 'client_credentials',
                'audience' => self::AUDIENCE,
            ];

            Log::info('🔑 Requesting NEW Pax8 access token');

            $resp = Http::asJson()->withoutVerifying()->post(self::TOKEN_URL, $payload);

            if (!$resp->successful()) {
                Log::error('Pax8 token fetch failed', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                    'headers' => $resp->headers(),
                ]);
                throw new \Exception('Token request failed: ' . $resp->status());
            }

            $json = $resp->json();
            $token = $json['access_token'] ?? null;
            $expiresIn = (int) ($json['expires_in'] ?? 86400); // Default 24 hours

            if (!$token) {
                Log::error('Pax8 token response missing access_token', ['response' => $json]);
                throw new \Exception('No access_token in response');
            }

            // Cache token with 30-minute buffer before expiry (typically 23.5 hours for 24h token)
            $cacheSeconds = max(300, $expiresIn - 1800);
            Cache::put('pax8_access_token', $token, now()->addSeconds($cacheSeconds));

            Log::info('✅ Pax8 access token obtained and cached', [
                'expires_in' => $expiresIn,
                'cache_duration' => $cacheSeconds,
            ]);

            return $token;
        } catch (\Throwable $e) {
            Log::error('Pax8 token request error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 🔄 Fetch & store all products from Pax8 API to local DB
     */
    public function syncProducts(): int
    {
        try {
            $token = $this->getAccessToken();
        } catch (\Throwable $e) {
            Log::error('Pax8: Could not obtain access token', ['error' => $e->getMessage()]);
            return 0;
        }

        $page = 1;
        $totalCount = 0;
        $maxPages = 100; // Safety limit

        Log::info('🔄 Starting Pax8 product sync');

        do {
            Log::info("📄 Fetching page {$page}");

            $response = Http::withToken($token)
                ->get(self::BASE_URL . '/products', [
                    'page' => $page,
                    'size' => 100,
                ]);

            if (!$response->successful()) {
                Log::error('Pax8 product fetch failed', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                break;
            }

            $responseData = $response->json();

            $products = $responseData['data'] ?? $responseData['content'] ?? [];
            $totalPages = $responseData['totalPages'] ?? null;
            $currentPage = $responseData['page'] ?? $page;

            if (empty($products)) {
                Log::info("No products found on page {$page}. Ending sync.");
                break;
            }

            Log::info("Processing " . count($products) . " products from page {$page}");

            foreach ($products as $p) {
                try {
                    Pax8Product::updateOrCreate(
                        ['pax8_id' => $p['id']],
                        [
                            'name' => $p['name'] ?? '',
                            'vendorName' => $p['vendorName'] ?? null,
                            'shortDescription' => $p['shortDescription'] ?? null,
                            'sku' => $p['sku'] ?? null,
                            'vendorSku' => $p['vendorSku'] ?? null,
                            'altVendorSku' => $p['altVendorSku'] ?? null,
                            'requiresCommitment' => $p['requiresCommitment'] ?? false,
                            'raw' => $p,
                        ]
                    );
                    $totalCount++;
                } catch (\Throwable $e) {
                    Log::error('Error saving product', [
                        'product_id' => $p['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Stop if we've processed all available pages
            if ($totalPages !== null && $currentPage >= $totalPages) {
                Log::info("Reached last page ({$totalPages}). Stopping.");
                break;
            }

            $page++;

            if ($page > $maxPages) {
                Log::warning("Reached max pages limit ({$maxPages}).");
                break;
            }

        } while (true);


        Log::info("✅ Pax8 sync complete", [
            'total_products' => $totalCount,
            'pages_processed' => $page - 1,
        ]);

        return $totalCount;
    }

    /**
     * 📄 List locally stored Pax8 products (with optional search & pagination)
     */
    public function listProducts(array $filters = [])
    {
        $query = Pax8Product::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vendorName', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 5000);
    }

    /**
     * 📦 Get a single local product by Pax8 ID
     */
    public function getProduct(string $productId): ?Pax8Product
    {
        return Pax8Product::where('pax8_id', $productId)->first();
    }

    /**
     * 💰 Get pricing info (still live from Pax8 — optional)
     */
    public function getProductPricing(string $productId, array $query = []): ?array
    {
        try {
            $token = $this->getAccessToken();
        } catch (\Throwable $e) {
            return null;
        }

        $resp = Http::withToken($token)
            ->get(self::BASE_URL . "/products/{$productId}/pricing", $query);

        return $resp->successful() ? $resp->json() : null;
    }

    /**
     * 🔗 Get dependencies info (still live from Pax8 — optional)
     */
    public function getProductDependencies(string $productId): ?array
    {
        try {
            $token = $this->getAccessToken();
        } catch (\Throwable $e) {
            return null;
        }

        $resp = Http::withToken($token)
            ->get(self::BASE_URL . "/products/{$productId}/dependencies");

        return $resp->successful() ? $resp->json() : null;
    }

    /**
     * 🚨 Handle webhook updates for product create/update/delete events
     */
    public function handleWebhook(array $payload): void
    {
        $product = $payload['data'] ?? null;
        if (!$product || !isset($product['id'])) {
            Log::warning('Pax8 webhook received invalid payload', ['payload' => $payload]);
            return;
        }

        Pax8Product::updateOrCreate(
            ['pax8_id' => $product['id']],
            [
                'name' => $product['name'] ?? '',
                'vendorName' => $product['vendor']['name'] ?? null,
                'shortDescription' => $product['shortDescription'] ?? null,
                'sku' => $product['sku'] ?? null,
                'vendorSku' => $product['vendorSku'] ?? null,
                'altVendorSku' => $product['altVendorSku'] ?? null,
                'requiresCommitment' => $product['requiresCommitment'] ?? false,
                'raw' => $product,
            ]
        );

        Log::info('🔄 Pax8 product updated via webhook', [
            'pax8_id' => $product['id'],
            'name' => $product['name'] ?? null,
        ]);
    }
}