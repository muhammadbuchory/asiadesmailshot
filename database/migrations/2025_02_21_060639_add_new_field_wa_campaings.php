<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->string('content')->nullable()->after('status'); // Tambahkan field phone
            $table->string('file')->nullable()->after('status'); // Tambahkan field phone
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropColumn('content'); // Hapus field phone jika rollback
            $table->dropColumn('file'); // Hapus field phone jika rollback
        });
    }
};
