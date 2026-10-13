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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('import_cost', 12, 2)->nullable()->after('cost');
            $table->decimal('transport_cost', 12, 2)->nullable()->after('import_cost');
            $table->decimal('packaging_cost', 12, 2)->nullable()->after('transport_cost');
            $table->decimal('ads_cost', 12, 2)->nullable()->after('packaging_cost');
            $table->decimal('other_cost', 12, 2)->nullable()->after('ads_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['import_cost', 'transport_cost', 'packaging_cost', 'ads_cost', 'other_cost']);
        });
    }
};
