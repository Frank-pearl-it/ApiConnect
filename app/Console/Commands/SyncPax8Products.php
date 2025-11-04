<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Pax8Service;

class SyncPax8Products extends Command
{
    protected $signature = 'pax8:sync-products';
    protected $description = 'Sync Pax8 products hourly from API';

    public function handle(Pax8Service $service): int
    {
        $count = $service->syncProducts();
        $this->info("✅ Synced {$count} Pax8 products.");
        return self::SUCCESS;
    }
}
