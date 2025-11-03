<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id('trans_id');
            $table->string('trans_ref')->unique();
            $table->enum('trans_type', ['in', 'out', 'adjustment']);
            $table->integer('trans_quantity');
            $table->date('trans_date');
            $table->text('trans_remarks')->nullable();
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders', 'po_id');
            $table->foreignId('trans_by')->constrained('users', 'user_id');
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_transactions');
    }
};