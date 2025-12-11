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
        Schema::create('transaction_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->enum('message_type', ['text', 'completion', 'review'])->default('text');
            $table->text('message')->nullable();
            $table->string('chat_image')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->boolean('is_read')->default(false);
            $table->index(['purchase_id', 'created_at']);
            $table->index(['purchase_id', 'is_read']);
            $table->index(['purchase_id', 'message_type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_messages');
    }
};
