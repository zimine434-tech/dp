<?php

namespace App\Providers;

use App\Models\Competition;
use App\Models\TrainingSession;
use App\Observers\CompetitionObserver;
use App\Observers\TrainingSessionObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\ScheduleParseGroups;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScheduleParseGroups::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = config('app.locale');
        App::setLocale($locale);
        Carbon::setLocale($locale);

        TrainingSession::observe(TrainingSessionObserver::class);
        Competition::observe(CompetitionObserver::class);

        Paginator::defaultView('pagination::tailwind');
    }
}
