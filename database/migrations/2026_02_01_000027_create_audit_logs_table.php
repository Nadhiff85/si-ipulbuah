<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Mencatat aktivitas pelanggan & admin untuk keperluan audit (UU PDP)
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('role')->nullable(); // pelanggan | admin | superadmin
            $table->string('action'); // login, logout, view_product, update_profile, create_order, dst
            $table->string('subject_type')->nullable(); // model terkait
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable(); // detail perubahan (before/after)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
