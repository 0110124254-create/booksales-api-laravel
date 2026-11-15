<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->text('shipping_address')->nullable();
            $table->string('midtrans_token')->nullable();
            $table->text('midtrans_redirect_url')->nullable();
            $table->string('fulfillment_status')->default('new')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn(['shipping_address', 'midtrans_token', 'midtrans_redirect_url', 'fulfillment_status']);
        });
    }
};
