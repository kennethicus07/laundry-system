
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->string('service_name', 150);
            $table->string('pricing_unit', 20);

            $table->decimal('estimated_quantity', 10, 2);
            $table->decimal('estimated_unit_price', 12, 2);
            $table->decimal('estimated_subtotal', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
