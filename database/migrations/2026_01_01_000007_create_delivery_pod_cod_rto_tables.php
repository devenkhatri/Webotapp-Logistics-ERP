<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('run_sheet_number')->unique(); // DRS-2026-0001
            $table->foreignId('delivery_agent_id')->constrained('users')->cascadeOnDelete();
            $table->date('run_date');
            $table->string('status')->default('created'); // created, out_for_delivery, completed, closed
            $table->integer('total_shipments')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->decimal('total_cod_to_collect', 12, 2)->default(0.00);
            $table->decimal('total_cod_collected', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_run_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->integer('sequence_order')->default(1);
            $table->string('status')->default('assigned'); // assigned, delivered, failed
            $table->timestamp('completed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_relation')->default('Self');
            $table->text('signature_data')->nullable(); // Base64 or path
            $table->string('pod_photo')->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('delivered_at')->useCurrent();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('attempt_no')->default(1);
            $table->string('reason_code'); // customer_unavailable, wrong_address, refused_to_accept, cash_not_ready, future_delivery_requested
            $table->text('remarks')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('cod_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_agent_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, upi, card
            $table->boolean('is_deposited_to_branch')->default(false);
            $table->timestamp('deposited_at')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cod_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('settlement_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_cod_amount', 12, 2);
            $table->decimal('total_deductions', 12, 2)->default(0.00); // courier fee deduction
            $table->decimal('net_payable', 12, 2);
            $table->string('payment_mode')->default('bank_transfer'); // bank_transfer, cheque, cash
            $table->string('transaction_reference')->nullable();
            $table->string('status')->default('pending'); // pending, approved, paid
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('processed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cod_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->decimal('cod_amount', 12, 2);
            $table->decimal('deduction_fee', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('rto_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('rto_awb')->unique();
            $table->string('return_reason');
            $table->string('status')->default('initiated'); // initiated, in_transit, received_at_hub, delivered_to_merchant
            $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_to_merchant_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rto_records');
        Schema::dropIfExists('cod_settlement_items');
        Schema::dropIfExists('cod_settlements');
        Schema::dropIfExists('cod_collections');
        Schema::dropIfExists('delivery_attempts');
        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('delivery_run_shipments');
        Schema::dropIfExists('delivery_runs');
    }
};
