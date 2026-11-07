<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('method', 30);
            $table->string('status', 30)->default('pending_verification');

            // QR transaction details
            $table->string('reference_number')->nullable();
            $table->string('proof_path')->nullable();

            // Prevent duplicate requests from being submitted
            $table->uuid('idempotency_key')->nullable()->unique();

            // Staff who recorded the payment
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Staff who verified the QR transaction
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('received_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['invoice_id', 'status']);
            $table->index(['method', 'reference_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};