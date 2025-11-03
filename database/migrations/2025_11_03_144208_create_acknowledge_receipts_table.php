<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acknowledge_receipts', function (Blueprint $table) {
            $table->id('ar_id');
            $table->string('ar_ref')->unique();
            $table->text('ar_remarks')->nullable();
            $table->enum('ar_status', ['issued', 'received', 'cancelled'])->default('issued');
            $table->date('issued_date');
            $table->foreignId('req_id')->constrained('requisitions', 'req_id');
            $table->foreignId('issued_by')->constrained('users', 'user_id');
            $table->foreignId('issued_to')->constrained('users', 'user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acknowledge_receipts');
    }
};