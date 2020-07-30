<?php

namespace Ysfkaya\IdeasoftApi;

use Illuminate\Support\ServiceProvider;

class IdeasoftApiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->app['config']->get('services.ideasoft');

        $this->app->singleton('ideasoft.oauth', function ($app) use ($config) {
            return new OAuth(
                $app['request'], $config['store_name'], $config['client_id'], $config['client_secret'],
                $config['redirect_uri'], $config['httpOptions']
            );
        });

        $this->app->singleton('ideasoft', function ($app) use ($config) {
            $ideasoft = new Ideasoft($config['store_name']);

            return $ideasoft->setAuthenticator($app['ideasoft.oauth']);
        });

        $this->app->bind(Ideasoft::class, 'ideasoft');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
