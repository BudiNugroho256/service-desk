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
        Schema::create('tblm_ticket_priority', function (Blueprint $table) {
            $table->id('id_ticket_priority');
            $table->string('tingkat_priority')->index();
            $table->string('tingkat_dampak')->index();
            $table->string('tingkat_urgensi')->index();
            $table->integer('sla_duration_normal');
            $table->integer('sla_duration_escalation');
            $table->integer('sla_duration_thirdparty');
            $table->text('ticket_priority_description')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblm_ticket_priority');
    }
};
