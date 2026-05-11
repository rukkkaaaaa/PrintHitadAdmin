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
        Schema::create('advertisement_type_has_advertisement_sizes', function (Blueprint $table) {
            $table->unsignedBigInteger('advertisement_type_id');
            $table->unsignedBigInteger('advertisement_size_id');
            $table->primary(['advertisement_type_id', 'advertisement_size_id']);
                $table->foreign('advertisement_type_id')->references('id')->on('advertisement_types')->onDelete('cascade')->name('adv_type_has_size_type_fk');
                $table->foreign('advertisement_size_id')->references('id')->on('advertisement_sizes')->onDelete('cascade')->name('adv_type_has_size_size_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_type_has_advertisement_sizes');
    }
};
