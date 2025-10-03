<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('phone_id');
            $table->string('waba_id');
            $table->text('access_token');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'phone_id']);
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['SDR', 'CLOSER', 'SUPORTE']);
            $table->text('prompt')->nullable();
            $table->json('tools_enabled')->nullable();
            $table->float('temperature')->default(0.4);
            $table->string('language')->default('pt-BR');
            $table->timestamps();
            $table->unique(['tenant_id', 'role']);
        });

        Schema::create('knowledge_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pdf', 'url', 'faq']);
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('vector_id')->nullable();
            $table->timestamps();
        });

        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('trigger');
            $table->json('condition')->nullable();
            $table->json('action');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->json('mon_sun');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('business_hours');
        Schema::dropIfExists('automations');
        Schema::dropIfExists('knowledge_items');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('whatsapp_accounts');
    }
};

