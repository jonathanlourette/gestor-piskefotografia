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
        Schema::table('order_photos', function (Blueprint $table) {
            $table->string('original_s3_path', 500)->nullable()->after('s3_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_photos', function (Blueprint $table) {
            $table->dropColumn('original_s3_path');
        });
    }
};
