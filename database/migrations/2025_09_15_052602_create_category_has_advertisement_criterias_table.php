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
        Schema::create('category_has_advertisement_criterias', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('advertisement_criteria_id');
            $table->primary(['category_id', 'advertisement_criteria_id']);
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->name('cat_has_adv_crit_cat_fk');
                $table->foreign('advertisement_criteria_id')->references('id')->on('advertisement_criterias')->onDelete('cascade')->name('cat_has_adv_crit_adv_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_has_advertisement_criterias');
    }
};
