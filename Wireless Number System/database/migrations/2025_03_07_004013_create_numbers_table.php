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
        Schema::create('numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->nullable();
            $table->foreignId('carrier_id')->index()->nullable();
            $table->foreignId('area_id')->index()->nullable();
            $table->foreignId('city_id')->index()->nullable();
            $table->string('phone_number')->nullable();
            $table->string('pin')->nullable();
            $table->string('account_number')->nullable();
            $table->date('expiry')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->boolean('is_used')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numbers');
    }
};
