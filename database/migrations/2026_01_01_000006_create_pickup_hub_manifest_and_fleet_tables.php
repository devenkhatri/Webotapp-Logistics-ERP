<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pickup_code')->unique();
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->text('pickup_address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->date('scheduled_date');
            $table->string('time_slot')->default('morning'); // morning, afternoon, evening
            $table->integer('expected_packages')->default(1);
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, assigned, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pickup_request_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_number')->unique();
            $table->string('model');
            $table->string('type')->default('van'); // bike, van, light_truck, heavy_truck
            $table->decimal('capacity_kg', 10, 2)->default(1000.00);
            $table->string('insurance_expiry')->nullable();
            $table->string('status')->default('available'); // available, on_trip, maintenance, inactive
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('license_number')->unique();
            $table->string('status')->default('active'); // active, on_duty, off_duty, inactive
            $table->timestamps();
        });

        Schema::create('shipment_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('origin_hub_id')->constrained('hubs')->cascadeOnDelete();
            $table->foreignId('destination_hub_id')->constrained('hubs')->cascadeOnDelete();
            $table->string('bag_number')->unique();
            $table->string('seal_number')->nullable();
            $table->decimal('weight_kg', 8, 2)->default(0.00);
            $table->string('status')->default('open'); // open, sealed, dispatched, received, unpacked
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bag_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bag_id')->constrained('shipment_bags')->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('hub_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_id')->constrained('hubs')->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('scan_type'); // inbound, sorting, bagging, outbound
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('manifest_number')->unique();
            $table->foreignId('source_hub_id')->constrained('hubs')->cascadeOnDelete();
            $table->foreignId('destination_hub_id')->constrained('hubs')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft'); // draft, dispatched, in_transit, received
            $table->integer('total_bags')->default(0);
            $table->integer('total_shipments')->default(0);
            $table->decimal('total_weight', 10, 2)->default(0.00);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('manifest_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bag_id')->nullable()->constrained('shipment_bags')->nullOnDelete();
            $table->string('status')->default('loaded'); // loaded, missing, damaged, received
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifest_shipments');
        Schema::dropIfExists('manifests');
        Schema::dropIfExists('hub_scans');
        Schema::dropIfExists('bag_shipments');
        Schema::dropIfExists('shipment_bags');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('pickup_request_shipments');
        Schema::dropIfExists('pickup_requests');
    }
};
