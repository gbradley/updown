<?php

namespace Gbradley\Updown\Laravel;

use GBradley\Updown\Client as Updown;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\CommandFinished;
use \Event;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/updown.php' => config_path('updown.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/config/routes.php');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Ensure that each resolved instance of the Updown client is configured with the API key and app token.
        $this->app->singleton(Updown::class, function ($app) {
            return new Updown(config('updown.api_key'), config('updown.app_token'));
        });

        // If enabled and an app token is supplied, watch for changes in maintenance mode.
        if (config('updown.maintenance.disable_checks') && config('updown.app_token')) {
            $this->watchForMaintainence();
        }
    }

    /**
     * Watch for the Artisan up/down comands and toggle the check status as needed.
     */
    protected function watchForMaintainence()
    {

        // Disable the check before putting the application into maintenance mode.
        Event::listen(CommandStarting::class, function($event) {
            if ($event->command == 'down') {
                app(Updown::class)->check()->disable();
            }
        });

        // Enable the check after exiting maintenance mode.
        Event::listen(CommandFinished::class, function($event) {
            if ($event->command == 'up') {
                app(Updown::class)->check()->enable();
            }
        });
    }
}
