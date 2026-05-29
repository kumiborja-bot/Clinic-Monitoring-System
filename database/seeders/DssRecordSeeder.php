<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DssRecord;

class DssRecordSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = storage_path('app/csv/dss.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("DSS CSV file not found at {$csvPath}!");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // read and skip headers

        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            // Ensure row is valid and not empty
            if (empty($row) || count($row) < 4) {
                continue;
            }

            $data[] = [
                'diagnosis' => trim($row[0]),
                'suggestion' => trim($row[1]),
                'follow_up' => isset($row[2]) ? trim($row[2]) : null,
                'medication' => isset($row[3]) ? trim($row[3]) : null,
                'duration_unit' => isset($row[4]) ? trim($row[4]) : null,
                'duration_value' => (isset($row[5]) && $row[5] !== '') ? (int) $row[5] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($file);

        // Chunk insert to avoid memory/query issues
        DB::table('dss_records')->truncate();
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('dss_records')->insert($chunk);
        }

        $count = count($data);
        $this->command->info("✅ DssRecordSeeder successfully imported {$count} decision support records!");
    }
}
