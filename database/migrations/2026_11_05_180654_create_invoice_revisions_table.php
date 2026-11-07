<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->unsignedInteger('revision_number');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);

            $table->string('reason');

            $table->foreignId('revised_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('revised_at');

            $table->timestamps();

            $table->unique(['invoice_id', 'revision_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_revisions');
    }
};