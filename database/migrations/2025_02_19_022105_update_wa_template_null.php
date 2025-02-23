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
        Schema::table('wa_templates', function (Blueprint $table) {
            $table->string('content')->nullable()->change();
            $table->string('file')->nullable()->change();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_templates', function (Blueprint $table) {
            $table->string('content')->nullable(false)->change();
            $table->string('file')->nullable(false)->change();
        });
    }
};
