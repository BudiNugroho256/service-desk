<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblt_ticket_log', function (Blueprint $table) {
            $table->text('change_summary')->nullable()->after('ticket_description');
        });
    }

    public function down(): void
    {
        Schema::table('tblt_ticket_log', function (Blueprint $table) {
            $table->dropColumn('change_summary');
        });
    }

};
