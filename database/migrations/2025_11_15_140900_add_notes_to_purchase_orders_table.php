<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('purchase_orders', 'notes')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('total_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_orders', 'notes')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
