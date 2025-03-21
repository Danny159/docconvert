<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileConversionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Blaspsoft\Doxswap\Facades\Doxswap;

class FileConversionController extends Controller
{
    /**
     * Show the file upload form.
     */
    public function index()
    {
        return view('fileconversion.index');
    }

    /**
     * Handle file upload and conversion.
     */
    public function convert(FileConversionRequest $request)
    {
        try {
            // Get the uploaded file
            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                throw new \Exception('Uploaded file is invalid or missing.');
            }

            $originalFileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileNameWithoutExt = str_replace(' ', '_', pathinfo($originalFileName, PATHINFO_FILENAME));

            // Create a unique filename to prevent overwrite
            $uniqueFileName = $fileNameWithoutExt . '_' . time() . '.' . $fileExtension;

            // Store the uploaded file in a public directory to ensure it exists
            $storagePath = 'uploads';
            $file->storeAs($storagePath, $uniqueFileName, 'local');

            // Get the full path to the stored file
            $fullPath = Storage::disk('local')->path($storagePath . '/' . $uniqueFileName);

            // Ensure file exists before proceeding
            if (!file_exists($fullPath)) {
                throw new \Exception("Stored file does not exist at path: {$fullPath}");
            }

            // Get the target format
            $format = $request->input('format', 'pdf');

            // Convert the file using Doxswap
            $convertedFile = Doxswap::convert($fullPath, $format);

            // Ensure the conversion produced an output file
            if (!$convertedFile || !isset($convertedFile->outputFile) || !file_exists($convertedFile->outputFile)) {
                throw new \Exception('Conversion failed to produce a valid output file.');
            }

            // Store conversion details in session for download
            session(['converted_file' => [
                'path' => $convertedFile->outputFile,
                'original_name' => $fileNameWithoutExt . '.' . $format,
                'format' => $format
            ]]);

            return redirect()->route('file.download');
        } catch (\Exception $e) {
            // Log detailed error for debugging
            \Log::error('File conversion error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->route('file.index')->with('error', 'File conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Download the converted file.
     */
    public function download()
    {
        if (!session()->has('converted_file')) {
            return redirect()->route('file.index')->with('error', 'No converted file found');
        }

        $fileDetails = session('converted_file');

        if (!file_exists($fileDetails['path'])) {
            return redirect()->route('file.index')->with('error', 'Converted file not found');
        }

        return response()->download(
            $fileDetails['path'],
            $fileDetails['original_name'],
            ['Content-Type' => $this->getContentType($fileDetails['format'])]
        )->deleteFileAfterSend(true);
    }

    /**
     * Get the content type based on file format.
     */
    private function getContentType(string $format): string
    {
        return match(strtolower($format)) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt' => 'application/vnd.ms-powerpoint',
            'txt' => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
