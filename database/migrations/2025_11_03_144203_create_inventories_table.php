<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id('inv_id');
            $table->string('inv_unit');
            $table->integer('inv_stock_quantity')->default(0);
            $table->date('inv_expire_date')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->integer('reorder_level')->default(0);
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
};