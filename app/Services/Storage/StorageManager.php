<?php

namespace App\Services\Storage;

use App\Contracts\StorageProvider;
use App\Models\Setting;
use App\Services\Storage\Providers\LocalProvider;
use App\Services\Storage\Providers\GcsProvider;
use App\Services\Storage\Providers\R2Provider;
use App\Services\Storage\Providers\MuxProvider;

class StorageManager
{
    protected $drivers = [];

    /**
     * Resolve the active storage driver.
     */
    public function driver(string $driver = null): StorageProvider
    {
        $driver = $driver ?: Setting::get('active_storage_driver', 'local');

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->resolve($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Instantiates the provider class for the given driver.
     */
    protected function resolve(string $driver): StorageProvider
    {
        switch ($driver) {
            case 'gcp':
                return new GcsProvider();
            case 'cloudflare':
                return new R2Provider();
            case 'mux':
                return new MuxProvider();
            case 'local':
            default:
                return new LocalProvider();
        }
    }
}
