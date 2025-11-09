<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id('req_item_id');
            $table->integer('req_item_quantity');
            $table->enum('req_item_status', ['pending', 'partially_fulfilled', 'fulfilled'])->default('pending');
            $table->string('item_unit');
            $table->foreignId('req_id')->constrained('requisitions', 'req_id')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->timestamps();
            
            // Indexes
            $table->index('req_id');
            $table->index('item_id');
            $table->index('req_item_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisition_items');
    }
};