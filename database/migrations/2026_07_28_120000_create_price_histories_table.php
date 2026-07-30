<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('old_price_unit', 12, 2);
            $table->decimal('new_price_unit', 12, 2);
            $table->decimal('old_price_wholesale', 12, 2)->nullable();
            $table->decimal('new_price_wholesale', 12, 2)->nullable();
            $table->decimal('old_cost_price', 12, 2)->nullable();
            $table->decimal('new_cost_price', 12, 2)->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('effective_at')->useCurrent();
            $table->timestamps();

            $table->index(['product_id', 'effective_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
