<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->foreignId('user_id')->index()->nullable();
            $table->foreignId('carrier_id')->index()->nullable();
            $table->foreignId('area_id')->index()->nullable();
            $table->foreignId('city_id')->index()->nullable();
            $table->string('order_type')->default(Order::ORDER_TYPE_BUY);
            $table->boolean('is_refunded')->default(false);
            $table->bigInteger('reject_qty')->default(0);
            $table->bigInteger('success_qty')->default(0);
            $table->bigInteger('total_qty')->default(0);
            $table->double('subtotal')->default(0);
            $table->string('currency')->default(Order::CURRENCY_USD);
            $table->double('total')->default(0);    
            $table->string('status')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
