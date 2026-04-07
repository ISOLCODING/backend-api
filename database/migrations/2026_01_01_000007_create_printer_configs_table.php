<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printer_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                             // nama identifikasi printer
            $table->enum('printer_type', ['bluetooth', 'network', 'usb'])->default('bluetooth');
            $table->string('device_address', 100)->nullable();       // MAC address / IP address
            $table->enum('paper_size', ['58mm', '80mm'])->default('80mm');
            $table->text('header_text')->nullable();                  // teks header struk
            $table->text('footer_text')->nullable();                  // teks footer struk
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printer_configs');
    }
};
