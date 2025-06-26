<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // 使用者ID (主鍵)
            $table->string('name', 255); // 使用者名稱
            $table->string('email', 255)->unique(); // 電子郵件 (唯一)
            $table->string('password', 255); // 加密密碼
            $table->timestamps(); // created_at 和 updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};


