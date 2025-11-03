<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id('pi_id');
            $table->integer('pi_quantity');
            $table->decimal('pi_unit_price', 10, 2);
            $table->decimal('pi_subtotal', 10, 2);
            $table->foreignId('po_id')->constrained('purchase_orders', 'po_id');
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
};