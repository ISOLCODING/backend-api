<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name', 150)->default('KasirinAja Store');
            $table->text('store_address')->nullable();
            $table->string('store_phone', 20)->nullable();
            $table->string('store_email', 100)->nullable();
            $table->string('store_logo')->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->string('currency_symbol', 10)->default('Rp');
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->integer('invoice_digits')->default(6);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
