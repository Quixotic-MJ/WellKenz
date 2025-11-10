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

            // Stock management
            $table->decimal('item_stock', 12, 3)->default(0);
            $table->date('item_expire_date')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->decimal('reorder_level', 12, 3)->default(0);
            $table->decimal('min_stock_level', 12, 3)->default(0);
            $table->decimal('max_stock_level', 12, 3)->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);   // <-- added here

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['item_stock', 'reorder_level']);
            $table->index('item_expire_date');
            $table->index('is_active');
            $table->index('is_custom');                     // <-- and its index
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};