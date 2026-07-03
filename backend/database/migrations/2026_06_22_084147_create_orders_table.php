<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')->unique();

            $table->foreignId('table_id')
                ->constrained('tables')
                ->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->enum('status', [
                'pending',
                'accepted',
                'preparing',
                'ready',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->enum('payment_method', ['cash', 'payway'])->default('cash');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->foreignId('coupon_id')->nullable();

            $table->timestamps();
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
