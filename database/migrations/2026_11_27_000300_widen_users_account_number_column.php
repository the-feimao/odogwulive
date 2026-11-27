<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Encrypted account numbers are base64 payloads, not the raw digits.
     * Databases that ran the original payout migration before its length was
     * corrected still have a 191-character column, which truncates the value.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'account_number')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('account_number', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'account_number')) {
            return;
        }

        // Return to the intended width from the original payout migration,
        // not the accidental 191-character default.
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_number', 255)->nullable()->change();
        });
    }
};
