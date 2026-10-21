<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->json('intake_checklist')->nullable()->after('intake_condition');
            $table->text('device_access_code_encrypted')->nullable()->after('internal_notes');
            $table->unsignedInteger('warranty_days')->nullable()->default(30)->after('closed_at');
            $table->dateTime('warranty_expires_at')->nullable()->after('warranty_days');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('approved_by_name', 150)->nullable()->after('accepted_at');
            $table->string('approved_ip', 45)->nullable()->after('approved_by_name');
        });

        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 180);
            $table->string('sku', 80)->nullable();
            $table->string('category', 80)->default('AUTRE');
            $table->string('brand_compatibility', 150)->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0.00);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'sku']);
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->foreignId('spare_part_id')->nullable()->after('quote_id')->constrained('spare_parts')->nullOnDelete();
            $table->decimal('purchase_cost_unit', 12, 2)->default(0.00)->after('unit_price');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('spare_part_id')->nullable()->after('invoice_id')->constrained('spare_parts')->nullOnDelete();
            $table->decimal('purchase_cost_unit', 12, 2)->default(0.00)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('spare_part_id');
            $table->dropColumn('purchase_cost_unit');
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('spare_part_id');
            $table->dropColumn('purchase_cost_unit');
        });

        Schema::dropIfExists('spare_parts');

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['approved_by_name', 'approved_ip']);
        });

        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'intake_checklist',
                'device_access_code_encrypted',
                'warranty_days',
                'warranty_expires_at',
            ]);
        });
    }
};
