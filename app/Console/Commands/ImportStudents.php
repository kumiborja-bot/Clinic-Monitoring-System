<?php

namespace App\Console\Commands;

use App\Services\StudentImportService;
use Illuminate\Console\Command;

class ImportStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:students {file? : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import students from a cleaned CSV file';

    /**
     * Execute the console command.
     */
    public function handle(StudentImportService $importService)
    {
        // Default to the user's specific desktop path if no file is provided
        $filePath = $this->argument('file') ?: 'C:\\Users\\Inonazarene\\Desktop\\MEDICAL\\cleaned_students.csv';

        $this->info("Starting student import from: {$filePath}...");

        $result = $importService->importFromPath($filePath);

        if ($result['success']) {
            $this->info("Import Completed!");
            $this->line("Imported: " . $result['imported_count'] . " students");
            $this->line("Skipped: " . $result['skipped_count'] . " rows");
            
            if (!empty($result['errors'])) {
                $this->warn("\nWarnings:");
                foreach ($result['errors'] as $error) {
                    $this->warn($error);
                }
            }
        } else {
            $this->error("Import failed: " . $result['message']);
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }
        }
    }
}
