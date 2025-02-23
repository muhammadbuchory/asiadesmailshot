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
        Schema::create('wa_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->integer('wa_campaigns_id')->nullable();
            $table->integer('subscriber_id')->nullable();
            $table->string('phone')->nullable();
            $table->text('response')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_outbox');
    }
};
