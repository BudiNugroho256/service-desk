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
        Schema::create('tblm_rootcause', function (Blueprint $table) {
            $table->id('id_rootcause');
            $table->foreignId('id_layanan')->nullable()->constrained('tblm_layanan', 'id_layanan');
            $table->string('nama_rootcause')->index();
            $table->text('rootcause_description')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblm_rootcause');
    }
};
