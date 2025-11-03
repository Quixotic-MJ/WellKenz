<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notif_id');
            $table->string('notif_title');
            $table->text('notif_content');
            $table->string('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};