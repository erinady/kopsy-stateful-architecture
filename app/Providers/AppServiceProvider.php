<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Illuminate\Support\Facades\URL;

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
        // Storage::extend('azure', function ($app, $config) {
        //     try {
        //         $connectionString = 'DefaultEndpointsProtocol=https;AccountName=' . $config['account_name'] . ';AccountKey=' . $config['account_key'] . ';EndpointSuffix=core.windows.net';
                
        //         $blobClient = BlobRestProxy::createBlobService($connectionString);
        //         $adapter = new AzureBlobStorageAdapter($blobClient, $config['container']);
        //         $filesystem = new Filesystem($adapter);
                
        //         return new FilesystemAdapter($filesystem, $adapter, $config);
        //     } catch (\Exception $e) {
        //         throw new \RuntimeException('Azure Blob Storage initialization failed: ' . $e->getMessage());
        //     }
        // });
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
