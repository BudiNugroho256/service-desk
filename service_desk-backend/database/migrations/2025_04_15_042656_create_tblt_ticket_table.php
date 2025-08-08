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
        Schema::create('tblt_ticket', function (Blueprint $table) {
            // Primary ID
            $table->id('id_ticket');

            // Foreign ID
            $table->foreignId('id_ticket_priority')->nullable()->constrained('tblm_ticket_priority', 'id_ticket_priority')->nullOnDelete();
            $table->foreignId('id_pic_ticket')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();
            $table->foreignId('id_end_user')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();
            $table->foreignId('id_divisi')->nullable()->constrained('tblm_divisi', 'id_divisi')->nullOnDelete();
            $table->foreignId('id_layanan')->nullable()->constrained('tblm_layanan', 'id_layanan')->nullOnDelete();
            $table->foreignId('id_solusi')->nullable()->constrained('tblm_solusi', 'id_solusi')->nullOnDelete();
            $table->foreignId('id_rootcause')->nullable()->constrained('tblm_rootcause', 'id_rootcause')->nullOnDelete();
            $table->foreignId('id_permintaan')->nullable()->constrained('tblm_permintaan', 'id_permintaan')->nullOnDelete();

            // Foreign Key with Timestamp
            $table->timestamp('created_on')->index()->nullable()->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();
            $table->timestamp('last_updated_on')->index()->nullable()->useCurrent();
            $table->foreignId('last_updated_by')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();
            $table->timestamp('escalation_date')->index()->nullable()->useCurrent();
            $table->foreignId('escalation_to')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();

            // Status with Timestamp
            $table->string('ticket_status')->index();
            $table->string('assigned_status')->index()->nullable();
            $table->timestamp('assigned_date')->index()->nullable();
            $table->timestamp('progress_date')->index()->nullable();
            $table->timestamp('closed_date')->index()->nullable();
            $table->timestamp('due_date')->index()->nullable();

            // Ticket Type
            $table->string('id_ticket_type', 20)->nullable()->unique()->index();
            $table->string('ticket_type')->nullable()->index();

            // Title and Description
            $table->string('ticket_title')->index()->nullable();
            $table->text('ticket_description')->nullable();
            $table->text('resolusi_description')->nullable();

            // Temporary Field
            $table->string('rootcause_awal')->nullable();
            $table->string('solusi_awal')->nullable();

            // Third Party Field
            $table->string('tp_pic_ticket')->nullable();
            $table->string('tp_pic_company')->nullable();
            $table->timestamp('tp_accepted_date')->index()->nullable();
            $table->integer('tp_sla_duration')->index()->nullable();
            $table->string('tp_rootcause')->nullable();
            $table->string('tp_solusi')->nullable();
            $table->timestamp('tp_closed_date')->index()->nullable();

            // Additional Support Fields
            $table->text('link_pendukung')->nullable();
            $table->text('screenshot_pendukung')->nullable();
            $table->json('teknisi_tambahan')->nullable();

            // Timestamp
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblt_ticket');
    }
};
