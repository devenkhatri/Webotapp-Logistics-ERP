<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique(); // USA, IND, GBR
            $table->string('iso2', 2)->unique(); // US, IN, GB
            $table->string('currency', 3)->default('USD');
            $table->string('phone_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. Local, Regional, National, North, Metro
            $table->string('code')->index(); // LOCAL, REG, NAT, METRO
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('postal_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('city');
            $table->string('state');
            $table->string('country_code', 3)->default('USA');
            $table->foreignId('zone_id')->nullable()->constrained('service_zones')->nullOnDelete();
            $table->boolean('is_pickup_serviceable')->default(true);
            $table->boolean('is_delivery_serviceable')->default(true);
            $table->boolean('is_cod_allowed')->default(true);
            $table->decimal('max_weight_kg', 8, 2)->default(50.00);
            $table->timestamps();
        });

        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Standard Tariff, Merchant Express, E-Commerce B2B
            $table->string('code')->index();
            $table->string('service_type')->default('standard'); // express, standard, heavy, same_day
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('rate_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_card_id')->constrained()->cascadeOnDelete();
            $table->string('zone_from')->default('LOCAL'); // LOCAL, METRO, REGIONAL, NATIONAL, ZONE_A, etc.
            $table->string('zone_to')->default('LOCAL');
            $table->decimal('base_weight_kg', 8, 2)->default(0.50);
            $table->decimal('base_rate', 10, 2)->default(10.00);
            $table->decimal('additional_weight_kg', 8, 2)->default(0.50);
            $table->decimal('additional_rate', 10, 2)->default(5.00);
            $table->decimal('cod_fixed_fee', 10, 2)->default(2.00);
            $table->decimal('cod_percent', 5, 2)->default(1.50);
            $table->decimal('fuel_surcharge_percent', 5, 2)->default(5.00);
            $table->decimal('handling_fee', 10, 2)->default(0.00);
            $table->decimal('gst_tax_percent', 5, 2)->default(18.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_slabs');
        Schema::dropIfExists('rate_cards');
        Schema::dropIfExists('postal_codes');
        Schema::dropIfExists('service_zones');
        Schema::dropIfExists('countries');
    }
};
