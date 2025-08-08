<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tblm_report', function (Blueprint $table) {
            $table->id('id_report');
            $table->string('nama_report');
            $table->string('inisial_report');
            $table->text('report_description')->nullable();
            $table->string('ukuran_kertas', 10)->nullable();
            $table->string('layout_kertas', 20)->nullable();
            $table->longText('query_report');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblm_report');
    }
};
