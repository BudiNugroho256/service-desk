<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tblt_ticket_tracking_comment_log', function (Blueprint $table) {
            $table->id('id_tracking_comment');

            $table->foreignId('id_ticket_tracking')->constrained('tblt_ticket_tracking', 'id_ticket_tracking')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('tblm_user', 'id_user')->nullOnDelete();

            $table->string('comment_type')->index(); // 'user', 'pic', 'system'
            $table->text('comment_text');
            $table->timestamp('comment_created_on')->nullable()->index()->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblt_ticket_tracking_comment_log');
    }
};