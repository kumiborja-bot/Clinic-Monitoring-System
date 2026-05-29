<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CsvDataController extends Controller
{
    public function searchPatient(Request $request)
    {
        $query = strtolower(trim($request->input('q', '')));
        if (!$query) {
            return response()->json([]);
        }

        $path = storage_path('app/csv/patients.csv');
        if (!file_exists($path)) {
            return response()->json([]);
        }

        $data = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (count($headers) == count($row)) {
                    $item = array_combine($headers, $row);
                    // Match by id_number or name
                    if (str_contains(strtolower($item['id_number'] ?? ''), $query) || str_contains(strtolower($item['name'] ?? ''), $query)) {
                        $data[] = $item;
                    }
                }
            }
            fclose($handle);
        }

        return response()->json($data);
    }

    public function getDss(Request $request)
    {
        return response()->json(\App\Models\DssRecord::all());
    }
}
