<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approved_request_items', function (Blueprint $table) {
            $table->id('req_item_id');
            $table->unsignedBigInteger('req_id')->index();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->string('item_unit');
            $table->decimal('requested_quantity', 10, 2);
            $table->decimal('approved_quantity', 10, 2);
            $table->string('req_ref')->nullable();
            $table->boolean('created_as_item')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('req_id')->references('req_id')->on('requisitions')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approved_request_items');
    }
};