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
        Schema::create('tblm_rating', function (Blueprint $table) {
            $table->id('id_rating');
            $table->string('nama_rating');
            $table->unsignedTinyInteger('nilai_rating')->default(0);
            $table->text('rating_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblm_rating');
    }
};
