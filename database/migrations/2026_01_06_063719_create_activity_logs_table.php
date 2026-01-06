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
        Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // Who performed the action
        $table->string('action_type'); // created, edited, deleted
        $table->string('model_type');  // Event, User, etc.
        $table->unsignedBigInteger('model_id')->nullable(); // ID of the item affected
        $table->text('description')->nullable(); // Details of the action
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
