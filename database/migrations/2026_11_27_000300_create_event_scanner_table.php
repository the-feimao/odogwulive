<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_scanner')) {
            Schema::create('event_scanner', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('scanner_id');
                $table->timestamps();

                // Legacy installs use signed int event IDs while newer ones use
                // unsigned bigints, so avoid cross-schema foreign keys here.
                // Assignment integrity is enforced by the controllers/API.
                $table->unique(['event_id', 'scanner_id']);
            });
        }

        // Keep the legacy single-scanner assignment visible to the new relation.
        DB::table('events')
            ->whereNotNull('scanner_id')
            ->orderBy('id')
            ->chunkById(500, function ($events) {
                $rows = [];

                foreach ($events as $event) {
                    $rows[] = [
                        'event_id' => $event->id,
                        'scanner_id' => $event->scanner_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($rows !== []) {
                    DB::table('event_scanner')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('event_scanner')) {
            Schema::dropIfExists('event_scanner');
        }
    }
};
