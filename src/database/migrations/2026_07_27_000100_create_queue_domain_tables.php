<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_queues', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status', 16)->default('CLOSED');
            $table->unsignedBigInteger('next_ticket_number')->default(1);
            $table->timestamps();
        });

        Schema::create('operator_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('queue_id')->constrained('service_queues')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->string('active_marker', 16)->nullable()->default('ACTIVE');
            $table->timestamp('assigned_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['operator_user_id', 'queue_id', 'active_marker'], 'operator_assignment_active_unique');
        });

        Schema::create('queue_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('queue_id')->constrained('service_queues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('operator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('ticket_number');
            $table->string('status', 16);
            $table->string('active_marker', 16)->nullable()->default('ACTIVE');
            $table->string('serving_marker', 16)->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('service_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(['queue_id', 'ticket_number']);
            $table->unique(['queue_id', 'user_id', 'active_marker'], 'queue_entry_active_user_unique');
            $table->unique(['operator_user_id', 'serving_marker'], 'queue_entry_serving_operator_unique');
            $table->index(['queue_id', 'status', 'joined_at', 'id'], 'queue_entry_fifo_index');
        });

        Schema::create('idempotency_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('operation');
            $table->string('idempotency_key', 128);
            $table->string('request_fingerprint', 64);
            $table->string('status', 16);
            $table->json('response_snapshot')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique(['user_id', 'operation', 'idempotency_key'], 'idempotency_scope_unique');
            $table->index('expires_at');
        });

        Schema::create('mcp_tool_calls', function (Blueprint $table): void {
            $table->id();
            $table->uuid('correlation_id')->index();
            $table->string('tool_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ai_agent_id')->nullable();
            $table->string('idempotency_key', 128)->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status', 16);
            $table->string('error_code')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_tool_calls');
        Schema::dropIfExists('idempotency_records');
        Schema::dropIfExists('queue_entries');
        Schema::dropIfExists('operator_assignments');
        Schema::dropIfExists('service_queues');
    }
};
