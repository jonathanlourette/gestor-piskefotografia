<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Converts status from enum string to integer + adds 'pago' status.
     *
     * Mapping: enviado(1), pago(2), revelando(3), concluido(4)
     */
    public function up(): void
    {
        // Step 1: Add temporary integer column
        DB::statement('ALTER TABLE orders ADD COLUMN status_int TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER uuid');

        // Step 2: Convert existing string values to integers
        DB::statement("UPDATE orders SET status_int = CASE status WHEN 'enviado' THEN 1 WHEN 'revelando' THEN 3 WHEN 'concluido' THEN 4 ELSE 1 END");

        // Step 3: Drop old enum column
        DB::statement('ALTER TABLE orders DROP COLUMN status');

        // Step 4: Rename integer column to status
        DB::statement('ALTER TABLE orders CHANGE COLUMN status_int status TINYINT UNSIGNED NOT NULL DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     * Reverts back to enum string (without 'pago').
     */
    public function down(): void
    {
        // Step 1: Add temporary string column
        DB::statement("ALTER TABLE orders ADD COLUMN status_str ENUM('enviado', 'revelando', 'concluido') NOT NULL DEFAULT 'enviado' AFTER uuid");

        // Step 2: Convert integers back to strings (pago → enviado as fallback)
        DB::statement("UPDATE orders SET status_str = CASE status WHEN 1 THEN 'enviado' WHEN 2 THEN 'enviado' WHEN 3 THEN 'revelando' WHEN 4 THEN 'concluido' ELSE 'enviado' END");

        // Step 3: Drop integer column
        DB::statement('ALTER TABLE orders DROP COLUMN status');

        // Step 4: Rename string column to status
        DB::statement("ALTER TABLE orders CHANGE COLUMN status_str status ENUM('enviado', 'revelando', 'concluido') NOT NULL DEFAULT 'enviado'");
    }
};
