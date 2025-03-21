<?php

namespace App\Http\Controllers;

use App\Services\DocumentConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentConverterController extends Controller
{
    private $documentConverter;

    public function __construct(DocumentConverterService $documentConverter)
    {
        $this->documentConverter = $documentConverter;
    }

    public function index()
    {
        return view('document-converter.index');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240', // Max 10MB
        ]);

        // // Create directories if they don't exist
        // if (!Storage::exists('private/pdfs')) {
        //     Storage::makeDirectory('private/pdfs');
        // }

        // if (!Storage::exists('private/converted')) {
        //     Storage::makeDirectory('private/converted');
        // }

        // Store the uploaded file
        $path = $request->file('pdf_file')->store('pdfs');
        $fullPath = storage_path('app/private/' . $path);

        // Convert PDF to Word
        try {
            $outputPath = $this->documentConverter->pdfToWord($fullPath);

            // Return the converted file as a download
            if (file_exists($outputPath)) {
                // Get original filename but with docx extension
                $originalName = pathinfo($request->file('pdf_file')->getClientOriginalName(), PATHINFO_FILENAME);
                $downloadName = $originalName . '.docx';

                return response()->download($outputPath, $downloadName)->deleteFileAfterSend(false);
            }

            return back()->with('error', 'Conversion failed. Please try again or with a different PDF.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during conversion: ' . $e->getMessage());
        }
    }
}
