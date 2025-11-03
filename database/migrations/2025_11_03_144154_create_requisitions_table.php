<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id('req_id');
            $table->string('req_ref')->unique();
            $table->text('req_purpose');
            $table->enum('req_priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('req_status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->date('req_date');
            $table->date('approved_date')->nullable();
            $table->foreignId('dept_id')->constrained('departments', 'dept_id');
            $table->foreignId('requested_by')->constrained('users', 'user_id');
            $table->foreignId('approved_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisitions');
    }
};