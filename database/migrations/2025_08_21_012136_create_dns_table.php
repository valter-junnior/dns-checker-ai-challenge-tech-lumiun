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
        Schema::create('dns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('queried_at');
            $table->string('domain', 255)->index();
            $table->string('client_ip', 45)->index();
            $table->enum('risk', ['safe','suspicious','malicious'])->nullable()->index();
            $table->float('ai_score')->nullable();
            $table->string('ai_model')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->json('enrichment')->nullable();
            $table->enum('status',['pending','enriched','classified','error'])->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dns');
    }
};
