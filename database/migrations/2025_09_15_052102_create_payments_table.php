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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('session_id');
            $table->string('success_indicator');
            $table->string('result');
            $table->timestamp('payment_date')->nullable();
            $table->enum('is_success', ['true', 'false'])->default('false');
            $table->timestamps();


            $table->foreign('advertisement_id')->references('id')->on('advertisements');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
