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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->default(1);
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('20:00:00');
            $table->unsignedInteger('slot_duration')->default(60);
            $table->decimal('price_per_slot', 8, 2)->default(0)->unsigned();
            $table->json('available_days')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
