<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('memos', function (Blueprint $table) {
            $table->id('memo_id');
            $table->string('memo_ref')->unique();
            $table->text('memo_remarks')->nullable();
            $table->date('received_date');
            $table->foreignId('received_by')->constrained('users', 'user_id');
            $table->foreignId('po_id')->constrained('purchase_orders', 'po_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('memos');
    }
};