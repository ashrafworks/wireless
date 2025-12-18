<?php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->double('charged_price')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('platform')->default(\App\Models\Transaction::WEB);
            $table->string('origin')->nullable();
            $table->string('currency')->nullable();
            $table->foreignId('order_id')->index()->nullable();
            $table->foreignId('wallet_id')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
