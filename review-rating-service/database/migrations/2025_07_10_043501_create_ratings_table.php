<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('report_id');
        $table->unsignedBigInteger('supervisor_id');
        $table->tinyInteger('rating')->unsigned(); // 1–5
        $table->text('comment')->nullable();
        $table->timestamps();

        $table->unique(['report_id', 'supervisor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
