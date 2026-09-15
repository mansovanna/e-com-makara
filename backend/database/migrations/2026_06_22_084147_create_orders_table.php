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

            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid',
                'refunded',
            ])->default('unpaid')->index();

            $table->enum('payment_method', ['cash', 'payway'])->default('cash');

            $table->decimal('total', 10, 2);

            $table->decimal('paid_amount', 10, 2)->nullable();

            // លុយអាប = paid_amount - total (គណនានៅ backend ពេលបង់រួច)
            $table->decimal('change_amount', 10, 2)->nullable();

            // ពេលបង់ប្រាក់ចប់ (null ប្រសិនបើនៅ unpaid)
            $table->timestamp('paid_at')->nullable();

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
