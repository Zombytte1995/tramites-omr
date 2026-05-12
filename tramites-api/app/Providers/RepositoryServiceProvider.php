<?php

namespace App\Providers;

use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use App\Repositories\Eloquent\InstitucionRepository;
use App\Repositories\Eloquent\TramiteRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            InstitucionRepositoryInterface::class,
            InstitucionRepository::class,
        );

        $this->app->bind(
            TramiteRepositoryInterface::class,
            TramiteRepository::class,
        );
    }
}
