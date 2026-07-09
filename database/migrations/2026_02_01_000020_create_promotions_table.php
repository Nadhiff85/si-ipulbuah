<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('discount_type', ['percent', 'nominal']);
            $table->decimal('discount_value', 12, 2);
            $table->enum('scope', ['product', 'category']);
            $table->unsignedBigInteger('target_id'); // product_id atau category_id tergantung scope
            $table->integer('min_purchase_qty')->nullable(); // syarat harga grosir
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
