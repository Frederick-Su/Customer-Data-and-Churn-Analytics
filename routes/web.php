<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

Route::get('/', function () {
    return view('upload');
});

Route::post('/analyze', function (Request $request) {
    // Running pandas/matplotlib/sklearn over a real spreadsheet is a
    // legitimately slow class of request; PHP's default 60s is tight for
    // that regardless of dataset size, so give this route more headroom.
    set_time_limit(300);

    $analysisId = Str::uuid()->toString();
    $request->validate([
        'excel' => 'required|mimes:xlsx,xls,csv'
    ]);

    // Save uploaded file
    $path = $request->file('excel')->store('uploads');

    // Python script
    $python = 'python';
    $script = base_path('python/analyze.py');
    $file = Storage::path($path);

    // Delete uploaded spreadsheets and results older than 2 hours
    $directories = Storage::disk('public')->directories('results');
    $expirationTime = now()->subHours(2)->timestamp;

    foreach ($directories as $dir) {
        // Clean up old result
        if (Storage::disk('public')->lastModified($dir) < $expirationTime) {
            Storage::disk('public')->deleteDirectory($dir);
        }
    }

    $uploadedFiles = Storage::files('uploads');

    foreach ($uploadedFiles as $oldFile) {
        // Clean up old spreadsheets
        if (Storage::lastModified($oldFile) < $expirationTime) {
            Storage::delete($oldFile);
        }
    }
    
    // Run Python
    $process = new Process(
        command: [$python, $script, $file, $analysisId],
        env: array_merge($_SERVER, $_ENV, [
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

    $output = $process->getOutput();

    // Read all images
    $images = collect(Storage::disk('public')->files("results/$analysisId"))
        ->filter(fn ($file) => str_ends_with($file, '.png'))
        ->map(function ($file) {
            return [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'url' => asset('storage/' . $file)
            ];
        })
        ->values();

    // Read all summaries
    $summaries = [];

    foreach (Storage::disk('public')->files("results/$analysisId") as $file) {

        if (str_ends_with($file, '.json')) {

            $name = pathinfo($file, PATHINFO_FILENAME);

            $summaries[$name] = json_decode(
                Storage::disk('public')->get($file),
                true
            );
        }
    }

    return view('upload', [
        'output' => $output,
        'images' => $images,
        'summaries' => $summaries,
        'analysisId' => $analysisId
    ]);
});