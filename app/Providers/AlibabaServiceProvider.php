<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AlibabaApiService;
use App\Services\AlibabaImportService;
use App\Services\AlibabaPricingService;
use App\Services\AlibabaOrderService;

class AlibabaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AlibabaApiService::class, function ($app) {
            return new AlibabaApiService();
        });

        $this->app->singleton(AlibabaImportService::class, function ($app) {
            return new AlibabaImportService($app->make(AlibabaPricingService::class));
        });

        $this->app->singleton(AlibabaPricingService::class, function ($app) {
            return new AlibabaPricingService();
        });

        $this->app->singleton(AlibabaOrderService::class, function ($app) {
            return new AlibabaOrderService($app->make(AlibabaApiService::class));
        });
    }

    public function boot()
    {
        //
    }
}
