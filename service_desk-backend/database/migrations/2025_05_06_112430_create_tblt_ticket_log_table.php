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
        Schema::create('tblt_ticket_log', function (Blueprint $table) {
            // Primary ID
            $table->id('id_ticket_log');

            // Jenis Ticket
            $table->string('id_ticket_type', 20)->nullable()->index();
            $table->string('ticket_type')->nullable()->index();
            
            // Foreign ID
            $table->foreignId('id_ticket')->nullable()->constrained('tblt_ticket', 'id_ticket')->cascadeOnDelete();
            $table->foreignId('id_pic_ticket')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();

            // Timestamp
            $table->timestamp('last_updated_on')->index()->nullable()->useCurrent();
            $table->foreignId('last_updated_by')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();
            $table->timestamp('escalation_date')->index()->nullable()->useCurrent();
            $table->foreignId('escalation_to')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();

            // Foreign Value
            $table->string('tingkat_dampak')->index()->nullable();
            $table->string('tingkat_urgensi')->index()->nullable();
            $table->string('tingkat_priority')->index()->nullable();
            $table->string('nama_user')->index()->nullable();
            $table->string('nama_divisi')->index()->nullable();
            $table->integer('sla_duration_normal')->index()->nullable();
            $table->integer('sla_duration_escalation')->index()->nullable();

            // Status with Timestamp
            $table->string('ticket_status')->index()->nullable();
            $table->string('assigned_status')->index()->nullable();
            $table->timestamp('assigned_date')->index()->nullable();
            $table->timestamp('closed_date')->index()->nullable();

            // Title and Description
            $table->string('ticket_title')->index()->nullable();
            $table->text('ticket_description')->nullable();
            $table->text('resolusi_description')->nullable();

            // Temporary Field
            $table->string('rootcause_awal')->index()->nullable();
            $table->string('solusi_awal')->index()->nullable();
            $table->string('tp_pic_ticket')->index()->nullable();
            $table->string('tp_pic_company')->index()->nullable();
            $table->timestamp('tp_accepted_date')->index()->nullable();
            $table->integer('tp_sla_duration')->index()->nullable();
            $table->string('tp_rootcause')->index()->nullable();
            $table->string('tp_solusi')->index()->nullable();
            $table->timestamp('tp_closed_date')->index()->nullable();

            // Timestamp
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblt_ticket_log');
    }
};
