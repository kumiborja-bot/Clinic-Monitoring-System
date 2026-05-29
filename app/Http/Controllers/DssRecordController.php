<?php

namespace App\Http\Controllers;

use App\Models\DssRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DssRecordController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $dssRecords = DssRecord::query()
            ->when($search, function ($query, $search) {
                $query->where('diagnosis', 'like', "%{$search}%")
                    ->orWhere('suggestion', 'like', "%{$search}%")
                    ->orWhere('medication', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dss/Index', [
            'dssRecords' => $dssRecords,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string|max:255',
            'suggestion' => 'required|string',
            'follow_up' => 'nullable|string|max:255',
            'medication' => 'nullable|string|max:255',
            'duration_unit' => 'nullable|string|max:255',
            'duration_value' => 'nullable|integer|min:0',
        ]);

        DssRecord::create($validated);

        return redirect()->route('dss.index')->with('success', 'Decision support record created successfully.');
    }

    public function update(Request $request, DssRecord $dss)
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string|max:255',
            'suggestion' => 'required|string',
            'follow_up' => 'nullable|string|max:255',
            'medication' => 'nullable|string|max:255',
            'duration_unit' => 'nullable|string|max:255',
            'duration_value' => 'nullable|integer|min:0',
        ]);

        $dss->update($validated);

        return redirect()->route('dss.index')->with('success', 'Decision support record updated successfully.');
    }

    public function destroy(DssRecord $dss)
    {
        $dss->delete();

        return redirect()->route('dss.index')->with('success', 'Decision support record deleted successfully.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        if ($handle === false) {
            return redirect()->back()->with('error', 'Unable to open CSV file.');
        }

        // Parse headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty.');
        }

        // Clean headers
        $headers = array_map(function ($header) {
            $header = preg_replace('/[\x{00EF}\x{00BB}\x{00BF}]/u', '', $header); // Remove BOM
            return strtolower(trim($header));
        }, $headers);

        // Find header map
        $map = [
            'diagnosis' => array_search('diagnosis', $headers),
            'suggestion' => array_search('suggestion', $headers),
            'follow_up' => array_search('follow_up', $headers),
            'medication' => array_search('medication', $headers),
            'duration_unit' => array_search('duration_unit', $headers),
            'duration_value' => array_search('duration_value', $headers),
        ];

        // Fallbacks if not exact matches
        if ($map['diagnosis'] === false) $map['diagnosis'] = array_search('disease', $headers);
        if ($map['suggestion'] === false) $map['suggestion'] = array_search('treatment', $headers);
        if ($map['medication'] === false) $map['medication'] = array_search('medicine', $headers);

        // If at least diagnosis and suggestion are mapped
        if ($map['diagnosis'] === false || $map['suggestion'] === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Invalid CSV. Must contain "diagnosis" and "suggestion" columns.');
        }

        $created = 0;
        $updated = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                $diagnosis = trim($row[$map['diagnosis']] ?? '');
                $suggestion = trim($row[$map['suggestion']] ?? '');

                if (empty($diagnosis) || empty($suggestion)) {
                    continue;
                }

                $followUp = $map['follow_up'] !== false ? trim($row[$map['follow_up']] ?? '') : null;
                $medication = $map['medication'] !== false ? trim($row[$map['medication']] ?? '') : null;
                $durationUnit = $map['duration_unit'] !== false ? trim($row[$map['duration_unit']] ?? '') : null;
                $durationValue = ($map['duration_value'] !== false && ($row[$map['duration_value']] ?? '') !== '') ? (int) $row[$map['duration_value']] : null;

                $record = DssRecord::updateOrCreate(
                    ['diagnosis' => $diagnosis],
                    [
                        'suggestion' => $suggestion,
                        'follow_up' => $followUp,
                        'medication' => $medication,
                        'duration_unit' => $durationUnit,
                        'duration_value' => $durationValue,
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', 'Failed to parse CSV: ' . $e->getMessage());
        }

        fclose($handle);

        return redirect()->back()->with('success', "Import completed! Added {$created} new cases and updated {$updated} existing cases.");
    }
}
