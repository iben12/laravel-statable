<?php

namespace Iben\Statable;

use Iben\Statable\Services\StateHistoryManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (! class_exists('CreateStateHistoryTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_state_history_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_state_history_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(StateHistoryManager::class, function () {
            return new StateHistoryManager();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            StateHistoryManager::class,
        ];
    }
}
