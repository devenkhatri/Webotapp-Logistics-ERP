<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rate_card_id')->nullable()->constrained('rate_cards')->nullOnDelete();
            $table->string('customer_type')->default('retail'); // retail, corporate, merchant
            $table->string('account_number')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->decimal('current_balance', 12, 2)->default(0.00);
            $table->string('billing_cycle')->default('weekly'); // daily, weekly, monthly, prepaid
            $table->string('api_key', 64)->nullable()->unique();
            $table->string('api_secret', 64)->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('status')->default('active'); // active, pending, suspended
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Office');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->text('address_line1');
            $table->text('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('country')->default('USA');
            $table->boolean('is_default_pickup')->default(false);
            $table->boolean('is_default_delivery')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};

