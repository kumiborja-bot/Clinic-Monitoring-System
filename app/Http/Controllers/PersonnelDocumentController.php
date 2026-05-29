<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\PersonnelDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonnelDocumentController extends Controller
{
    public function store(Request $request, Personnel $personnel)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('personnel-documents', 'public');

        $personnel->personnelDocuments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(PersonnelDocument $document)
    {
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy(PersonnelDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
