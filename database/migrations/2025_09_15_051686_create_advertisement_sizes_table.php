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
        Schema::create('advertisement_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_type_id');
            $table->string('advertisement_size_en');
            $table->string('advertisement_size_si');
            $table->boolean('is_active')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('img_url');
            $table->timestamps();


            $table->foreign('advertisement_type_id')->references('id')->on('advertisement_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_sizes');
    }
};
