<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transactions', 'memo_ref')) {
                $table->string('memo_ref', 255)->nullable()->after('trans_remarks');
            }
        });

        // Add foreign key to memos.memo_ref if not exists
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Ensure there is an index for memo_ref for FK creation
            $table->index('memo_ref', 'it_memo_ref_idx');
            // Some drivers require constraint names to be unique
            $table->foreign('memo_ref', 'it_memo_ref_fk')
                  ->references('memo_ref')->on('memos')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Drop FK and index if they exist
            try { $table->dropForeign('it_memo_ref_fk'); } catch (\Throwable $e) {}
            try { $table->dropIndex('it_memo_ref_idx'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('inventory_transactions', 'memo_ref')) {
                $table->dropColumn('memo_ref');
            }
        });
    }
};
