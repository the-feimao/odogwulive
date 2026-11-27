<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement', function (Blueprint $table) {
            if (! Schema::hasColumn('settlement', 'currency')) {
                $table->string('currency')->nullable()->after('payment_type');
            }
            if (! Schema::hasColumn('settlement', 'gateway_reference')) {
                $table->string('gateway_reference')->nullable()->after('payment_token');
            }
            if (! Schema::hasColumn('settlement', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('settlement', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('failure_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settlement', function (Blueprint $table) {
            foreach (['paid_at', 'failure_reason', 'gateway_reference', 'currency'] as $column) {
                if (Schema::hasColumn('settlement', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
