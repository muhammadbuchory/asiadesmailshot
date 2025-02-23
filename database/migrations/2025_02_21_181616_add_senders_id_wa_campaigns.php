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
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->integer('senders_id')->nullable()->after('wa_templates_id'); // Tambahkan field phone
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropColumn('senders_id'); // Hapus field phone jika rollback
        });
    }
};
