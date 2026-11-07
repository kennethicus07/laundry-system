<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_revision_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_revision_id')
                ->constrained('invoice_revisions')
                ->cascadeOnDelete();

            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);

            $table->timestamps();

            $table->index('invoice_revision_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_revision_items');
    }
};