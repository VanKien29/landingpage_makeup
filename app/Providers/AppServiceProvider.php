<?php

namespace App\Providers;

use App\Models\User;
use App\Models\WorkshopEvent;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Gate::define('manage-academy', fn (User $user): bool => strtolower($user->email) === strtolower(config('academy.admin_email')));
        RateLimiter::for('consultation-form', function (Request $request): Limit {
            $contact = Str::lower(trim((string) $request->input('contact')));

            return Limit::perMinute(5)->by('consultation|'.$request->ip().'|'.$contact);
        });
        RateLimiter::for('workshop-registration', function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by('workshop|'.$request->ip().'|'.$email);
        });
        View::composer('partials.header', function ($view): void {
            $view->with('publicWorkshop', WorkshopEvent::where('slug', config('workshop.slug'))->first());
        });
    }
}
