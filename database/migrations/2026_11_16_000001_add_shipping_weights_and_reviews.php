<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->unsignedInteger('weight_gram')->default(300)->after('stock');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->unsignedBigInteger('subtotal')->default(0)->after('total');
            $table->unsignedBigInteger('shipping_cost')->default(0)->after('subtotal');
            $table->unsignedBigInteger('shipping_destination_id')->nullable()->after('shipping_address');
            $table->string('shipping_destination')->nullable()->after('shipping_destination_id');
            $table->string('shipping_courier')->nullable()->after('shipping_destination');
            $table->string('shipping_service')->nullable()->after('shipping_courier');
            $table->string('shipping_etd')->nullable()->after('shipping_service');
        });

        DB::table('sales')->update(['subtotal' => DB::raw('total')]);

        Schema::create('book_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'book_id']);
            $table->index(['book_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reviews');

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn([
                'subtotal',
                'shipping_cost',
                'shipping_destination_id',
                'shipping_destination',
                'shipping_courier',
                'shipping_service',
                'shipping_etd',
            ]);
        });

        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn('weight_gram');
        });
    }
};
