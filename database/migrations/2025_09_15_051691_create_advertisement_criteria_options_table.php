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
        Schema::create('advertisement_criteria_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_criteria_id');
            $table->string('advertisement_criteria_option_name_en');
            $table->string('advertisement_criteria_option_name_si');
            $table->boolean('is_active')->default(1);
            $table->timestamps();


            $table->foreign('advertisement_criteria_id')->references('id')->on('advertisement_criterias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_criteria_options');
    }
};
