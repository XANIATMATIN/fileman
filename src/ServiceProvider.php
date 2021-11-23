<?php

namespace MatinUtils\FileMan;

use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
    }

    public function provides()
    {
        return [];
    }
}
