<?php

namespace App\Providers;

use App\Factories\FacebookFactory;
use App\Models\FacebookManager;
use App\Models\Facebook;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class FacebookServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $source = dirname(dirname(__DIR__)) . '/config/facebook.php';
        $this->app->configure('facebook');
        $this->mergeConfigFrom($source, 'facebook');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerManager();
        $this->registerConnection();
    }

    /**
     * Register the factory class.
     *
     * @return void
     */
    protected function registerFactory()
    {
        $this->app->singleton('facebook.factory', function () {
            return new FacebookFactory();
        });

        $this->app->alias('facebook.factory', FacebookFactory::class);
    }

    /**
     * Register the manager class.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('facebook', function (Container $app) {
            $config = $app['config'];
            $factory = $app['facebook.factory'];
            return new FacebookManager($config, $factory);
        });
        $this->app->alias('facebook', FacebookManager::class);
    }

    /**
     * Register connection.
     *
     * @return void
     */
    protected function registerConnection()
    {
        $this->app->bind('facebook.connection', function (Container $app) {
            $manager = $app['facebook'];
            return $manager->connection();
        });
        $this->app->alias('facebook.connection', Facebook::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'facebook',
            'facebook.factory',
            'facebook.connection'
        ];
    }
}
