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
        Schema::table('google_sheets', function (Blueprint $table) {
            $table->unsignedBigInteger('marketer_id')->nullable()->after('id')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_sheets', function (Blueprint $table) {
            $table->dropColumn('marketer_id');
        });
    }
};
