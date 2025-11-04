<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\Pax8Service;

class RegisterPax8Webhook extends Command
{
    protected $signature = 'pax8:register-webhook';
    protected $description = 'Register webhook with Pax8 for product updates';

    public function handle(Pax8Service $service): int
    {
        $token = $service->getAccessToken();
        if (!$token) {
            $this->error('Missing Pax8 access token');
            return self::FAILURE;
        }

        $resp = Http::withToken($token)->post('https://api.pax8.com/api/v2/webhooks', [
            'displayName' => 'Storefront Catalog Sync',
            'url' => config('app.url') . '/api/pax8/webhook',
            'webhookTopics' => [[
                'topic' => 'PRODUCT',
                'filters' => [
                    ['action' => 'ProductCreated'],
                    ['action' => 'ProductUpdated'],
                ],
            ]],
        ]);

        $this->info($resp->body());
        return self::SUCCESS;
    }
}
