<?php

namespace Thaumware\Portal\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Thaumware\Portal\Adapters\IlluminateAdapter;
use Thaumware\Portal\Portal;

// Define provider only when Illuminate ServiceProvider exists to avoid fatal errors
if (class_exists(BaseServiceProvider::class)) {
    class IlluminatePortalServiceProvider extends BaseServiceProvider
    {
        public function register(): void
        {
            // Instantiate adapter using Laravel facades
            $adapter = new IlluminateAdapter(
                DB::getFacadeRoot(),
                Http::getFacadeRoot(),
                Schema::getFacadeRoot()
            );

            // Install Portal in DI container
            Portal::install($adapter);
        }

        public function boot(): void
        {
            // Try to create tables non-blocking
            try {
                $adapter = app(\Thaumware\Portal\Contracts\StorageAdapter::class);
                if (method_exists($adapter, 'install')) {
                    $adapter->install();
                }
            } catch (\Exception $e) {
                // ignore: database not ready or adapter missing
            }
        }
    }
}
