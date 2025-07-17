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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->default(null)->constrained()->onDelete('set null');
            

            // about
            $table->text('about')->nullable()->default(null);
            $table->string('language')->nullable()->default('en');
            $table->string('contact_email')->nullable()->default(null);

            // social media
            $table->string('linkedin_url')->nullable()->default(null);
            $table->string('instagram_url')->nullable()->default(null);
            $table->string('twitter_url')->nullable()->default(null);
            $table->string('facebook_url')->nullable()->default(null);
            $table->string('youtube_url')->nullable()->default(null);
            $table->string('github_url')->nullable()->default(null);
            $table->string('website_url')->nullable()->default(null);

            // address
            $table->string('street')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('postal_code')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
