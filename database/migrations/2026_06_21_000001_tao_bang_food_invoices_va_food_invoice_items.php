<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            Schema::create('food_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_code')->unique();
                $table->foreignId('user_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
                $table->foreignId('ticket_id')->nullable()->constrained('ve_xem_phims')->nullOnDelete();
                $table->string('customer_name')->nullable();
                $table->string('customer_phone', 30)->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
                $table->string('payment_method', 100)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['payment_status', 'created_at']);
            });
        }

        if (! Schema::hasTable('food_invoice_items')) {
            Schema::create('food_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('food_invoice_id')->constrained('food_invoices')->cascadeOnDelete();
                $table->string('food_name');
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_price', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('food_invoice_items');
        Schema::dropIfExists('food_invoices');
    }
};
