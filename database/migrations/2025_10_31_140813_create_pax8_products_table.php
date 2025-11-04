<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // unified internal ID
            $table->string('external_id')->nullable()->index(); // Pax8 / Autotask ID
            $table->enum('source', ['pax8', 'autotask', 'internal'])->default('pax8');
            $table->string('name');
            $table->string('vendor_name')->nullable();
            $table->longText('short_description')->nullable();
            $table->string('sku')->nullable();
            $table->string('vendor_sku')->nullable();
            $table->string('alt_vendor_sku')->nullable();
            $table->boolean('requires_commitment')->nullable(); 
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw')->nullable(); // full original payload (Pax8 / Autotask / internal)
            $table->timestamps();

            $table->unique(['external_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
