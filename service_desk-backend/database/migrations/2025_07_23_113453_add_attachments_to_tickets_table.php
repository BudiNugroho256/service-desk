<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tblt_ticket', function (Blueprint $table) {
            $table->json('ticket_attachments')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tblt_ticket', function (Blueprint $table) {
            $table->dropColumn('ticket_attachments');
        });
    }
};
