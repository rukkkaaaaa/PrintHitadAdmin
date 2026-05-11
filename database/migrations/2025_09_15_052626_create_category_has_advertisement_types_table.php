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
        Schema::create('category_has_advertisement_types', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('advertisement_type_id');
            $table->primary(['category_id', 'advertisement_type_id']);
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->name('cat_has_adv_type_cat_fk');
                $table->foreign('advertisement_type_id')->references('id')->on('advertisement_types')->onDelete('cascade')->name('cat_has_adv_type_type_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_has_advertisement_types');
    }
};
