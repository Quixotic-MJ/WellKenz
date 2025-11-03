<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('po_id');
            $table->string('po_ref')->unique();
            $table->enum('po_status', ['draft', 'ordered', 'delivered', 'cancelled'])->default('draft');
            $table->date('order_date');
            $table->text('delivery_address');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('sup_id')->constrained('suppliers', 'sup_id');
            $table->foreignId('req_id')->constrained('requisitions', 'req_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
};