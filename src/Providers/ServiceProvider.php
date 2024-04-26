<?php

namespace SMSkin\LaravelSaga\Providers;

use Illuminate\Support\Facades\Config;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Command\SagaCache;
use SMSkin\LaravelSaga\Command\SagaCacheClear;
use SMSkin\LaravelSaga\Contracts\ISagaLogger;
use SMSkin\LaravelSaga\Contracts\ISagaRepository;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->commands([
            SagaCache::class,
            SagaCacheClear::class,
        ]);

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->registerConfigs();
        $this->registerMigrations();
    }

    public function register()
    {
        $this->mergeConfigs();
        $this->registerStorageRepository();
        $this->registerLogger();
        $this->registerListeners();
    }

    private function registerListeners()
    {
        $filePath = SagaCache::getCacheFile();
        if (file_exists($filePath)) {
            $this->registerEventsFromCache();
            return;
        }

        $this->registerEvents();
    }

    private function registerEventsFromCache()
    {
        $events = require SagaCache::getCacheFile();
        foreach ($events as $eventClass => $listeners) {
            foreach ($listeners as $listenerClass) {
                $this->app['events']->listen($eventClass, $listenerClass);
            }
        }
    }

    private function registerEvents()
    {
        $sagas = Config::get('saga.state-machines');
        foreach ($sagas as $sagaClass) {
            /**
             * @var $saga BaseSaga
             * @noinspection PhpUnhandledExceptionInspection
             */
            $saga = $this->app->make($sagaClass);
            $events = $saga->getEvents();

            foreach ($events as $eventClass) {
                $this->app['events']->listen($eventClass, $sagaClass);
            }
        }
    }

    private function registerStorageRepository()
    {
        $repositoryType = Config::get('saga.repositories.default');
        $repositoryClass = Config::get('saga.repositories')[$repositoryType]['class'];
        $this->app->bind(ISagaRepository::class, $repositoryClass);
    }

    private function registerLogger()
    {
        $this->app->bind(ISagaLogger::class, Config::get('saga.logger'));
    }

    private function registerConfigs()
    {
        $this->publishes([
            __DIR__ . '/../../config/saga.php' => $this->app->configPath('saga.php'),
        ], 'config');
    }

    private function registerMigrations()
    {
        $repositoryType = Config::get('saga.repositories.default');
        if ($repositoryType !== 'database') {
            return;
        }

        if (empty(glob($this->app->databasePath('migrations/*_create_sagas_table.php')))) {
            $this->publishes([
                __DIR__ . '/../../database/migrations/create_sagas_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His', time()) . '_create_sagas_table.php'),
            ], 'migrations');
        }
    }

    private function mergeConfigs()
    {
        $configPath = __DIR__ . '/../../config/saga.php';
        $this->mergeConfigFrom($configPath, 'saga');
    }
}
