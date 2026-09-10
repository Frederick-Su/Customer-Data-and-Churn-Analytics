<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->call(function () {
                $this->deleteOldFiles('uploads', 'local');
                $this->deleteOldFiles('results', 'public');
            })->daily();
        });
    }

    protected function deleteOldFiles(string $directory, string $disk): void
    {
        $cutoff = now()->subWeek()->timestamp;

        foreach (Storage::disk($disk)->allFiles($directory) as $file) {
            if (Storage::disk($disk)->lastModified($file) < $cutoff) {
                Storage::disk($disk)->delete($file);
            }
        }

        foreach (Storage::disk($disk)->allDirectories($directory) as $folder) {
            $files = Storage::disk($disk)->allFiles($folder);

            if (empty($files)) {
                Storage::disk($disk)->deleteDirectory($folder);
            }
        }
    }
}
