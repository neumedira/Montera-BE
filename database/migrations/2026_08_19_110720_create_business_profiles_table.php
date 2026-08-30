<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('cafe_name', 150);
            $table->text('address')->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('instagram', 50)->nullable();
            $table->string('tiktok', 50)->nullable();
            $table->string('banner_image_url')->nullable(); // Kolom untuk menyimpan poster/banner
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
