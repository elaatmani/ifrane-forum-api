<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // Add the new column for 'spent_in'
            $table->dateTime('spent_in')->nullable()->after('started_at');
            
        });
    
        // Now that the column exists, update the values
        DB::statement('UPDATE ads SET spent_in = started_at');
    
        // Drop the old 'started_at' column
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    
        // Drop the 'stopped_at' column
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('stopped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // Add the 'started_at' column back
            $table->dateTime('started_at')->nullable()->after('spent_in');
            
        });
    
        // Now that the column exists, copy the values back
        DB::statement('UPDATE ads SET started_at = spent_in');
    
        // Drop the 'spent_in' column
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('spent_in');
        });
    
        // Add the 'stopped_at' column back
        Schema::table('ads', function (Blueprint $table) {
            $table->dateTime('stopped_at')->nullable()->default(null);
        });
    }
};
