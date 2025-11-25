<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tblt_ticket_tracking', function (Blueprint $table) {
            // Primary ID
            $table->id('id_ticket_tracking');

            // Jenis Ticket
            $table->string('id_ticket_type', 20)->index()->nullable();
            $table->string('ticket_type')->index()->nullable();

            // Foreign ID
            $table->foreignId('id_ticket')->nullable()->constrained('tblt_ticket', 'id_ticket')->cascadeOnDelete();
            $table->foreignId('id_pic_ticket')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();

            // Main Content
            $table->string('tracking_status')->index();
            $table->text('ticket_comment');
            $table->text('pic_comment')->nullable();
            $table->timestamp('tracking_created_on')->index()->nullable();
            $table->timestamp('comment_created_on')->index()->nullable();
            $table->text('user_comment')->nullable();
            $table->text('cancel_comment')->nullable();

            // Timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblt_ticket_tracking');
    }
};