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
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            // Which table are we tracking
            $table->string('trackable_type');
            // Which record from the table are we referencing
            $table->integer('trackable_id')->unsigned();
            // Who made the action
            $table->integer('actor_id')->unsigned();
            // fields that have changed
            $table->json('fields')->nullable();
            $table->string('event')->nullable()->default('updated');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history');
    }
};
