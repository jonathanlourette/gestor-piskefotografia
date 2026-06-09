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
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('title');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);
            $table->string('password');
            $table->string('cover_photo_path', 500)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('photos_count')->default(0);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->string('s3_path', 500);
            $table->string('thumbnail_path', 500);
            $table->string('original_name');
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('sort_order');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['gallery_id', 'sort_order']);
            $table->index('gallery_id');
        });

        Schema::create('gallery_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_photo_id')->constrained('gallery_photos')->cascadeOnDelete();
            $table->string('visitor_token', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['gallery_photo_id', 'visitor_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_favorites');
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('galleries');
    }
};
