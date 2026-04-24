<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_user_id')->constrained('wa_users')->cascadeOnDelete();
            $table->enum('action', ['check_in', 'check_out']);
            $table->timestamp('performed_at');
            $table->boolean('synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->json('api_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['synced', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_attendance_logs');
    }
};
