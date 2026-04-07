<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name', 50)->default('PPN');
            $table->decimal('tax_rate', 5, 2)->default(11.00);  // persentase pajak
            $table->enum('tax_type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->enum('rounding', ['up', 'down', 'normal'])->default('normal');
            $table->boolean('is_active')->default(false);        // pajak aktif atau tidak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
