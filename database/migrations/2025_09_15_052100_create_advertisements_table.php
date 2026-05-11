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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('city_id');
            $table->text('advertisement_description');
            $table->date('publish_date');
            $table->enum('publication', ['hitad_print', 'lahipita']);
            $table->tinyInteger('web_combined_ad')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();


            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->foreign('city_id')->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
