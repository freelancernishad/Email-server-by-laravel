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
        Schema::table('email_configurations', function (Blueprint $table) {
            $table->string('type')->default('smtp')->after('id');
            $table->string('api_key')->nullable()->after('type');
            // Make existing fields nullable for API type configurations
            $table->string('host')->nullable()->change();
            $table->integer('port')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configurations', function (Blueprint $table) {
            $table->dropColumn(['type', 'api_key']);
            $table->string('host')->nullable(false)->change();
            $table->integer('port')->nullable(false)->change();
        });
    }
};
