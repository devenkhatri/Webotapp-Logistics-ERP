<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awb_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('prefix')->default('CX');
            $table->unsignedBigInteger('next_number')->default(100001);
            $table->string('suffix')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('origin_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('destination_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('current_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            
            // Identification
            $table->string('awb_number')->unique();
            $table->string('reference_number')->nullable()->index(); // Merchant Order No
            $table->string('shipment_type')->default('forward'); // forward, reverse, return
            $table->string('service_type')->default('standard'); // express, standard, heavy, same_day
            $table->string('payment_type')->default('prepaid'); // prepaid, cod, to_pay, corporate_credit
            
            // Sender info
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_email')->nullable();
            $table->text('sender_address');
            $table->string('sender_city');
            $table->string('sender_state')->nullable();
            $table->string('sender_postal_code');
            
            // Receiver info
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_email')->nullable();
            $table->text('receiver_address');
            $table->string('receiver_city');
            $table->string('receiver_state')->nullable();
            $table->string('receiver_postal_code');
            
            // Package & Metrics
            $table->string('content_description')->default('General Goods');
            $table->integer('pieces_count')->default(1);
            $table->decimal('actual_weight', 8, 2)->default(0.50); // kg
            $table->decimal('length', 8, 2)->default(10.00); // cm
            $table->decimal('width', 8, 2)->default(10.00);
            $table->decimal('height', 8, 2)->default(10.00);
            $table->decimal('volumetric_weight', 8, 2)->default(0.20);
            $table->decimal('chargeable_weight', 8, 2)->default(0.50);
            
            // Financials
            $table->decimal('declared_value', 12, 2)->default(0.00);
            $table->decimal('cod_amount', 12, 2)->default(0.00);
            $table->decimal('base_freight', 10, 2)->default(0.00);
            $table->decimal('fuel_surcharge', 10, 2)->default(0.00);
            $table->decimal('handling_fee', 10, 2)->default(0.00);
            $table->decimal('cod_fee', 10, 2)->default(0.00);
            $table->decimal('insurance_fee', 10, 2)->default(0.00);
            $table->decimal('gst_tax', 10, 2)->default(0.00);
            $table->decimal('total_freight', 10, 2)->default(0.00);
            $table->string('payment_status')->default('pending'); // pending, paid, collected, refunded
            
            // Assignment & Operations
            $table->foreignId('pickup_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delivery_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('booked')->index();
            // booked, pickup_scheduled, picked_up, in_transit, received_at_hub, sorted, bagged, dispatched, out_for_delivery, delivered, failed_attempt, rto_initiated, rto_in_transit, rto_delivered, cancelled
            
            $table->string('current_location')->nullable();
            $table->string('delivery_otp', 6)->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->text('pod_signature')->nullable();
            $table->string('pod_photo')->nullable();
            $table->string('recipient_relation')->nullable(); // self, family, security, neighbor
            $table->string('recipient_name')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('failed_reason')->nullable();
            $table->integer('attempt_count')->default(0);
            
            // Return / RTO
            $table->string('return_awb_number')->nullable()->index();
            $table->string('return_reason')->nullable();
            
            $table->text('special_instructions')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shipment_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('package_barcode')->index();
            $table->decimal('weight', 8, 2);
            $table->decimal('length', 8, 2)->default(10.00);
            $table->decimal('width', 8, 2)->default(10.00);
            $table->decimal('height', 8, 2)->default(10.00);
            $table->decimal('volumetric_weight', 8, 2)->default(0.20);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('status_title');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('event_time')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_tracking_events');
        Schema::dropIfExists('shipment_packages');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('awb_sequences');
    }
};
