<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('s3_path', 500);
            $table->string('original_name');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_photos');
    }
};
