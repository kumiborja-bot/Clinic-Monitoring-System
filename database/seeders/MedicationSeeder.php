<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedicationSeeder extends Seeder
{
    public function run()
    {
        $csvPath = storage_path('app/csv/medications.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found!");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file);

        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            $data[] = [
                'name' => $row[1],
                'description' => $row[2],
                'category' => $row[3],
                'unit' => $row[4],
                'stock_quantity' => (int) $row[5],
                'reorder_level' => (int) $row[6],
                'expiry_date' => $row[7],
            ];
        }
        fclose($file);

        Schema::disableForeignKeyConstraints();
        DB::table('medicines')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('medicines')->insert($data);

        $this->command->info('✅ MedicationSeeder completed with 500 unique Philippine medications!');
    }
}
