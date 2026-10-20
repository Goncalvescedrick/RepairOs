<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('reference', 50);
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('RECU');
            $table->string('priority', 20)->default('NORMALE'); // BASSE, NORMALE, HAUTE, URGENTE
            $table->text('reported_issue');
            $table->text('intake_condition')->nullable();
            $table->text('accessories_received')->nullable();
            $table->dateTime('deposit_date');
            $table->dateTime('estimated_completion_date')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('client_notes')->nullable();
            $table->string('public_tracking_token', 80)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'reference']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'deposit_date']);
        });

        Schema::create('repair_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained('repair_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'repair_ticket_id']);
        });

        Schema::create('repair_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained('repair_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('intervention_type', 50)->default('DIAGNOSTIC'); // DIAGNOSTIC, OPERATION, TEST, AUTRE
            $table->text('symptoms_observed')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('identified_cause')->nullable();
            $table->text('work_performed');
            $table->text('test_results')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('customer_summary')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'repair_ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_interventions');
        Schema::dropIfExists('repair_status_histories');
        Schema::dropIfExists('repair_tickets');
    }
};
