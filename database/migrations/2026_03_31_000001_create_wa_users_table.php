<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_users', function (Blueprint $table) {
            $table->id();
            $table->string('phone_e164')->unique();
            $table->string('name')->nullable();
            $table->string('api_base_url');
            $table->string('api_token')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_users');
    }
};
