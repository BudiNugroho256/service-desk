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
        Schema::create('tblt_eskalasi_pihak_ketiga', function (Blueprint $table) {
            $table->id('id_eskalasi_pihak_ketiga');
            $table->foreignId('id_pihak_ketiga')->nullable()->constrained('tblm_pihak_ketiga', 'id_pihak_ketiga')->nullOnDelete();
            $table->string('tp_pic_ticket')->nullable();
            $table->string('tp_problem_description')->nullable();
            $table->string('tp_sla_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblt_eskalasi_pihak_ketiga');
    }
};
