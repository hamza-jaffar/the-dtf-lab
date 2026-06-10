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
        // Add pricing_type to order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('pricing_type')->default('per_sqin')->after('quantity');
            // 'per_sqin'   → width × height × rate × qty
            // 'per_piece'  → rate × qty (flat price per piece)
        });

        // Add price_override to orders (allows manual total adjustment after bargaining)
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price_override', 12, 2)->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('price_override');
        });
    }
};
