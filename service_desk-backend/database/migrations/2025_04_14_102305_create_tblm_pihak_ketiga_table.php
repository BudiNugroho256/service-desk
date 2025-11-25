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
        Schema::create('tblm_pihak_ketiga', function (Blueprint $table) {
            $table->id('id_pihak_ketiga');
            $table->string('nama_perusahaan');
            $table->string('perusahaan_description')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblm_pihak_ketiga');
    }
};
