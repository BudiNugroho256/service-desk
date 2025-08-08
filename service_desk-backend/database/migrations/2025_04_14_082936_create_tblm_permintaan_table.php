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
        Schema::create('tblm_permintaan', function (Blueprint $table) {
            $table->id('id_permintaan');
            $table->foreignId('id_layanan')->nullable()->constrained('tblm_layanan', 'id_layanan');
            $table->string('nama_permintaan')->index();
            $table->text('permintaan_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblm_permintaan');
    }
};