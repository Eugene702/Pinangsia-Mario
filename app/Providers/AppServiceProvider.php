<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
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
        if (app()->isLocal()) {
            if (request()->input('test_time') === 'clear') {
                session()->forget('test_time');
            }

            $testTimeString = request('test_time') ?? session('test_time');

            if ($testTimeString) {
                try {
                    $testTime = Carbon::parse($testTimeString);
                    Carbon::setTestNow($testTime);

                    session(['test_time' => $testTimeString]);
                } catch (\Exception $e) {
                    session()->forget('test_time');
                }
            }
        }
    }
}
