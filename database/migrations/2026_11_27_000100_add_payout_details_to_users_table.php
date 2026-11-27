<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'bank_code')) {
                $table->string('bank_code')->nullable()->after('country');
            }
            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('bank_code');
            }
            if (! Schema::hasColumn('users', 'account_number')) {
                // Laravel's schema default is 191 here; encrypted values need more room.
                $table->string('account_number', 255)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('users', 'account_name')) {
                $table->string('account_name')->nullable()->after('account_number');
            }
            if (! Schema::hasColumn('users', 'payout_currency')) {
                $table->string('payout_currency')->nullable()->after('account_name');
            }
            if (! Schema::hasColumn('users', 'payout_verified_at')) {
                $table->timestamp('payout_verified_at')->nullable()->after('payout_currency');
            }
            if (! Schema::hasColumn('users', 'paystack_recipient_code')) {
                $table->string('paystack_recipient_code')->nullable()->after('payout_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'paystack_recipient_code',
                'payout_verified_at',
                'payout_currency',
                'account_name',
                'account_number',
                'bank_name',
                'bank_code',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
