<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payment_setting', 'paystack')) {
            Schema::table('payment_setting', function (Blueprint $table) {
                $table->integer('paystack')->nullable()->default(0)->after('flutterwave');
            });
        }

        if (! Schema::hasColumn('payment_setting', 'paystackPublicKey')) {
            Schema::table('payment_setting', function (Blueprint $table) {
                $table->string('paystackPublicKey')->nullable()->after('raveSecretKey');
            });
        }

        if (! Schema::hasColumn('payment_setting', 'paystackSecretKey')) {
            Schema::table('payment_setting', function (Blueprint $table) {
                $table->string('paystackSecretKey')->nullable()->after('paystackPublicKey');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payment_setting', function (Blueprint $table) {
            $table->dropColumn([
                'paystack',
                'paystackPublicKey',
                'paystackSecretKey',
            ]);
        });
    }
};
