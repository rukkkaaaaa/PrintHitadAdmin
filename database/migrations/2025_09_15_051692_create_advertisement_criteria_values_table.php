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
        Schema::create('advertisement_criteria_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_id');
            $table->unsignedBigInteger('advertisement_criteria_id');
            $table->text('advertisement_criteria_option_value');
            $table->timestamps();


            $table->foreign('advertisement_id')->references('id')->on('advertisements');
            $table->foreign('advertisement_criteria_id')->references('id')->on('advertisement_criterias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_criteria_values');
    }
};
