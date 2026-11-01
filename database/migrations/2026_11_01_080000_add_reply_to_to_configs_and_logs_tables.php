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
            $table->string('reply_to_address')->nullable()->after('from_name');
            $table->string('reply_to_name')->nullable()->after('reply_to_address');
        });

        Schema::table('email_logs', function (Blueprint $table) {
            $table->string('reply_to')->nullable()->after('bcc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configurations', function (Blueprint $table) {
            $table->dropColumn(['reply_to_address', 'reply_to_name']);
        });

        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropColumn(['reply_to']);
        });
    }
};

