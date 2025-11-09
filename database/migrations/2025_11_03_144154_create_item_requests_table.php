<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id('item_req_id');
            $table->string('item_req_name');
            $table->string('item_req_unit');
            $table->integer('item_req_quantity');
            $table->text('item_req_description')->nullable();
            $table->enum('item_req_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users', 'user_id');
            $table->foreignId('approved_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('item_req_status');
            $table->index('requested_by');
            $table->index('approved_by');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_requests');
    }
};