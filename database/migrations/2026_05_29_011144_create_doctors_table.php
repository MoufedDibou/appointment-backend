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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('specialty_id')->constrained();
            $table->string('license_number')->unique();
            $table->text('qualifications');
            $table->integer('years_of_experience');
            $table->decimal('consultation_fee', 8, 2);
            $table->text('available_days')->nullable(); // JSON or comma-separated
            $table->time('available_from')->default('09:00:00');
            $table->time('available_to')->default('17:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
