<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    // Delete previous results
    Storage::disk('public')->delete(
        Storage::disk('public')->files("results/$analysisId")
    );
    
    // Run Python
    $output = shell_exec(
        "\"$python\" \"$script\" \"$file\" \"$analysisId\" 2>&1"
    );

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