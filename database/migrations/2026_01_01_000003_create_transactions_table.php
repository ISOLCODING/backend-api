<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // kasir
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number', 30)->unique();
            $table->string('payment_method', 20)->default('cash'); // cash, qris, transfer
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->decimal('change', 15, 2)->default(0);
            $table->string('payment_status', 20)->default('paid'); // paid, pending, refunded
            $table->string('notes')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();

            $table->index('invoice_number');
            $table->index('user_id');
            $table->index('created_at');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
