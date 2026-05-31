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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('ticket_code')->unique();

            $table->string('movie_title');

            $table->string('cinema_name')->nullable();

            $table->string('room_name')->nullable();

            $table->string('seat_code')->nullable();

            $table->dateTime('show_time')->nullable();

            $table->decimal('total_price', 12, 2)->default(0);

            $table->decimal('refund_amount', 12, 2)->default(0);

            $table->enum('type', [
                'online',
                'offline'
            ])->default('online');

            $table->enum('status', [
                'paid',
                'cancelled',
                'used'
            ])->default('paid');

            $table->dateTime('booked_at')->nullable();

            $table->dateTime('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
