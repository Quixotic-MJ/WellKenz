<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->string('item_unit');
            $table->foreignId('cat_id')->constrained('categories', 'cat_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};