<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hackathons = DB::table('hackathons')->get();

        foreach ($hackathons as $hackathon) {
            $updates = [];

            // Fix technologies field
            if (!empty($hackathon->technologies) && is_string($hackathon->technologies)) {
                // If it's a comma-separated string, convert to JSON array
                if (strpos($hackathon->technologies, ',') !== false) {
                    $techs = array_map('trim', explode(',', $hackathon->technologies));
                    $updates['technologies'] = json_encode($techs);
                } else {
                    // Single technology, wrap in array
                    $updates['technologies'] = json_encode([$hackathon->technologies]);
                }
            }

            // Fix sponsors field if it's a string
            if (!empty($hackathon->sponsors) && is_string($hackathon->sponsors)) {
                if (strpos($hackathon->sponsors, ',') !== false) {
                    $sponsors = array_map('trim', explode(',', $hackathon->sponsors));
                    $updates['sponsors'] = json_encode($sponsors);
                } else {
                    $updates['sponsors'] = json_encode([$hackathon->sponsors]);
                }
            }

            // Fix prizes field if it's a string
            if (isset($hackathon->prizes) && !empty($hackathon->prizes) && is_string($hackathon->prizes)) {
                if (strpos($hackathon->prizes, ',') !== false) {
                    $prizes = array_map('trim', explode(',', $hackathon->prizes));
                    $updates['prizes'] = json_encode($prizes);
                } else {
                    $updates['prizes'] = json_encode([$hackathon->prizes]);
                }
            }

            if (!empty($updates)) {
                DB::table('hackathons')
                    ->where('id', $hackathon->id)
                    ->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse, this is a data fix
    }
};
