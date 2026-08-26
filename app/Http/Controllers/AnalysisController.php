<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalysisController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    // --- CUSTOMER ANALYSIS ---
    public function show()
    {
        return view('upload');
    }

    public function analyze(Request $request)
    {
        $request->validate(['excel' => 'required|mimes:xlsx,xls,csv|max:10240']);
        $analysisId = Str::uuid()->toString();
        $path = $request->file('excel')->store('uploads');

        // Uses the default 'analyze.py'
        $this->runPythonScript($path, $analysisId);

        return view('upload', [
            'output' => 'Success',
            'images' => $this->getImages($analysisId),
            'summaries' => $this->getSummaries($analysisId),
            'analysisId' => $analysisId
        ]);
    }

    // --- TICKETING ANALYSIS (NEW) ---
    public function showTicketing()
    {
        return view('upload-tickets');
    }

    public function analyzeTicketing(Request $request)
    {
        $request->validate(['excel' => 'required|mimes:xlsx,xls,csv|max:10240']);
        $analysisId = Str::uuid()->toString();
        $path = $request->file('excel')->store('uploads');

        // Pass the new script name here!
        $this->runPythonScript($path, $analysisId, 'analyze_tickets.py');

        return view('upload-tickets', [
            'output' => 'Success',
            'images' => $this->getImages($analysisId),
            'summaries' => $this->getSummaries($analysisId),
            'analysisId' => $analysisId
        ]);
    }

    // --- SHARED METHODS ---
    // Note the new $scriptFile parameter with a default value
    private function runPythonScript(string $path, string $analysisId, string $scriptFile = 'analyze.py'): void
    {
        $process = new Process(
            command: ['python', base_path("python/{$scriptFile}"), Storage::path($path), $analysisId],
            env: array_merge(getenv(), [
                'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
                'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
                'MPLCONFIGDIR' => storage_path('app/temp/matplotlib')
            ])
        );

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function getImages(string $analysisId): Collection
    {
        // (Keep your existing getImages logic)
        return collect(Storage::disk('public')->files("results/$analysisId"))
            ->filter(fn ($file) => str_ends_with($file, '.png'))
            ->map(fn ($file) => [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'url' => asset('storage/' . $file)
            ])->values();
    }

    private function getSummaries(string $analysisId): array
    {
         // (Keep your existing getSummaries logic)
        $summaries = [];
        $files = Storage::disk('public')->files("results/$analysisId");

        foreach ($files as $file) {
            if (str_ends_with($file, '.json')) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $summaries[$name] = json_decode(Storage::disk('public')->get($file), true);
            }
        }

        return $summaries;
    }
}