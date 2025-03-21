<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class DocumentConverterService
{
    /**
     * LibreOffice binary path
     */
    protected $libreOfficePath;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Get LibreOffice path from config or use default
        $this->libreOfficePath = Config::get('docconvert.libreoffice_path', 'soffice');
    }

    /**
     * Convert PDF file to Word document while preserving text styling
     *
     * @param string $pdfPath Path to the PDF file
     * @return string Path to the converted Word document
     */
    public function pdfToWord(string $pdfPath)
    {
        // Create output filename with .docx extension
        $outputFileName = Str::random(40) . '.docx';
        $outputPath = storage_path('app/private/converted/' . $outputFileName);

        // Ensure the output directory exists
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Method 1: Using LibreOffice (if available on server)
        // This often provides better formatting preservation
        if ($this->checkLibreOfficeInstalled()) {
            return $this->convertWithLibreOffice($pdfPath, $outputPath);
        }

        // Method 2: Using Ghostscript + Calibre (alternative option)
        // Only use if LibreOffice isn't available
        return $this->convertWithGhostscriptAndCalibre($pdfPath, $outputPath);
    }

    /**
     * Convert PDF to Word using LibreOffice
     */
    private function convertWithLibreOffice(string $pdfPath, string $outputPath)
    {
        Log::info('Converting PDF to Word using LibreOffice', ['input' => $pdfPath, 'output' => $outputPath, 'libreoffice_path' => $this->libreOfficePath]);

        $tempDir = dirname($outputPath);

        // Using LibreOffice headless mode to convert with better formatting preservation
        $process = new Process([
            $this->libreOfficePath,
            '--headless',
            '--convert-to', 'docx:MS Word 2007 XML',
            '--outdir', $tempDir,
            $pdfPath
        ]);

        $process->setTimeout(300); // 5 minutes timeout
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error('LibreOffice conversion failed', [
                'error' => $process->getErrorOutput(),
                'command' => $process->getCommandLine()
            ]);
            // Fall back to alternative method if LibreOffice fails
            return $this->convertWithGhostscriptAndCalibre($pdfPath, $outputPath);
        }

        // LibreOffice outputs to the original filename but with .docx extension
        $baseFileName = pathinfo(basename($pdfPath), PATHINFO_FILENAME);
        $libreOfficeOutput = $tempDir . '/' . $baseFileName . '.docx';

        // Rename to our desired output path if needed
        if ($libreOfficeOutput !== $outputPath && file_exists($libreOfficeOutput)) {
            rename($libreOfficeOutput, $outputPath);
        }

        return $outputPath;
    }

    /**
     * Alternative conversion using Ghostscript and Calibre
     */
    private function convertWithGhostscriptAndCalibre(string $pdfPath, string $outputPath)
    {
        Log::info('Converting PDF to Word using Ghostscript and Calibre', [
            'input' => $pdfPath,
            'output' => $outputPath
        ]);

        // First convert PDF to HTML to preserve more formatting
        $htmlPath = str_replace('.docx', '.html', $outputPath);

        // Use pdf2htmlEX if available
        $pdf2htmlProcess = new Process([
            'pdf2htmlEX',
            '--zoom', '1.5',
            '--embed', 'cfijo',
            '--dest-dir', dirname($htmlPath),
            '--output', basename($htmlPath),
            $pdfPath
        ]);

        $pdf2htmlProcess->setTimeout(300);
        $pdf2htmlProcess->run();

        if (!$pdf2htmlProcess->isSuccessful()) {
            // Fallback to pdftohtml
            $pdfToHtmlProcess = new Process([
                'pdftohtml',
                '-s', // generate single HTML file
                '-noframes', // don't generate frames
                '-enc', 'UTF-8',
                '-zoom', '1.5', // better quality
                $pdfPath,
                $htmlPath
            ]);

            $pdfToHtmlProcess->setTimeout(300);
            $pdfToHtmlProcess->run();
        }

        // Now convert HTML to DOCX using Calibre's ebook-convert
        if (file_exists($htmlPath)) {
            $ebookConvertProcess = new Process([
                'ebook-convert',
                $htmlPath,
                $outputPath,
                '--enable-heuristics'
            ]);

            $ebookConvertProcess->setTimeout(300);
            $ebookConvertProcess->run();

            // Clean up intermediate files
            @unlink($htmlPath);
        } else {
            // If all else fails, try simple conversion
            $this->simpleConversion($pdfPath, $outputPath);
        }

        return $outputPath;
    }

    /**
     * Simple last-resort conversion
     */
    private function simpleConversion(string $pdfPath, string $outputPath)
    {
        Log::info('Falling back to simple PDF to Word conversion', ['input' => $pdfPath, 'output' => $outputPath]);

        // Use external service or simplest conversion method
        // This is a placeholder for your default/original conversion logic
        // You can add your current implementation here

        // Example using Pandoc (if installed)
        $process = new Process([
            'pandoc',
            '-f', 'pdf',
            '-t', 'docx',
            '-o', $outputPath,
            $pdfPath
        ]);

        $process->setTimeout(300);
        $process->run();
    }

    /**
     * Check if LibreOffice is installed and available
     */
    private function checkLibreOfficeInstalled()
    {
        // First try the configured path
        try {
            $process = new Process([$this->libreOfficePath, '--version']);
            $process->run();
            
            if ($process->isSuccessful()) {
                Log::info('LibreOffice detected using configured path', [
                    'path' => $this->libreOfficePath,
                    'version' => trim($process->getOutput())
                ]);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Error checking for LibreOffice with configured path', [
                'path' => $this->libreOfficePath,
                'error' => $e->getMessage()
            ]);
        }

        // Try common paths if configured path failed
        $commonPaths = [
            'soffice',
            '/usr/bin/soffice',
            '/usr/local/bin/soffice',
            '/opt/libreoffice/program/soffice',
            '/Applications/LibreOffice.app/Contents/MacOS/soffice'
        ];

        foreach ($commonPaths as $path) {
            if ($path === $this->libreOfficePath) {
                continue; // Skip already tried path
            }
            
            try {
                $process = new Process([$path, '--version']);
                $process->run();
                
                if ($process->isSuccessful()) {
                    // Found LibreOffice, update the path
                    $this->libreOfficePath = $path;
                    Log::info('LibreOffice detected at alternate path', [
                        'path' => $path,
                        'version' => trim($process->getOutput())
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                // Just continue to next path
            }
        }

        // As a last resort, check if the binary exists using file_exists
        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                $this->libreOfficePath = $path;
                Log::info('LibreOffice binary found but not tested', ['path' => $path]);
                return true;
            }
        }

        Log::warning('LibreOffice not detected on system');
        return false;
    }
}
