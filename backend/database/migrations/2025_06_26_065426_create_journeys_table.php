<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->bigIncrements('journey_id'); // 行程ID (主鍵)
            $table->unsignedBigInteger('plan_id'); // 所屬計畫ID (外鍵)
            $table->date('date'); // 行程日期
            $table->time('time')->nullable(); // 行程時間
            $table->string('journey_title', 255); // 行程標題
            $table->json('links')->nullable(); // 相關連結 (JSON)
            $table->json('image')->nullable(); // 圖片URLs (JSON)
            $table->text('notes')->nullable(); // 備註
            $table->timestamps();

            // 外鍵約束
            $table->foreign('plan_id')->references('plan_id')->on('plans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};

