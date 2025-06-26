<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('plan_id'); // 計畫ID (主鍵)
            $table->unsignedBigInteger('user_id'); // 使用者ID (外鍵)
            $table->string('plan_title', 255); // 計畫標題
            $table->timestamps();

            // 外鍵約束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

