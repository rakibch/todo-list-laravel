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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // creator
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['Todo', 'In Progress', 'Done'])->default('Todo');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
