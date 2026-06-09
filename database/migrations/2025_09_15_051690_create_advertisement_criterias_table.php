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
        Schema::create('advertisement_criterias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('advertisement_criteria_name_en');
            $table->string('advertisement_criteria_name_si');
            $table->enum('field_type', ['dropdown', 'textarea', 'radio']);
            $table->boolean('is_active')->default(1);
            $table->timestamps();


            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_criterias');
    }
};
