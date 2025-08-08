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
        Schema::create('tblm_layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->foreignId('id_user_assigned')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();
            $table->string('group_layanan')->index()->nullable();
            $table->string('nama_layanan')->index()->nullable();
            $table->string('status_layanan')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblm_layanan');
    }
};
