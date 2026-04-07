<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->enum('type', ['in', 'out', 'adj_plus', 'adj_minus', 'transfer', 'return']);
            $table->integer('quantity');
            $table->integer('stock_before')->default(0); // stok sebelum perubahan
            $table->integer('stock_after')->default(0);  // stok setelah perubahan
            $table->enum('reason', ['purchase', 'sale', 'damaged', 'expired', 'opname', 'transfer', 'return', 'other'])->default('other');
            $table->string('reference', 50)->nullable(); // nomor invoice/referensi
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
