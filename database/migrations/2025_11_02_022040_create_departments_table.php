<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id('dept_id');
            $table->string('dept_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
};