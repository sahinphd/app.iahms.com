<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gcp:setup-cors {origin=*}', function ($origin) {
    $this->info("Initializing GCS CORS Configuration...");

    $projectId = \App\Models\Setting::get('gcp_project_id');
    $bucketName = \App\Models\Setting::get('gcp_bucket');
    $keyFileJson = \App\Models\Setting::get('gcp_key_file');

    if (!$projectId || !$bucketName || !$keyFileJson) {
        $this->error("GCP Configuration is missing in settings. Please configure GCP Project ID, Bucket, and Key File in the Admin Settings first.");
        return 1;
    }

    try {
        $config = ['projectId' => $projectId];
        $keyFileData = json_decode($keyFileJson, true);
        if (is_array($keyFileData)) {
            $config['keyFile'] = $keyFileData;
        } else {
            $this->error("GCP Key File JSON is invalid.");
            return 1;
        }

        $client = new \Google\Cloud\Storage\StorageClient($config);
        $bucket = $client->bucket($bucketName);

        if (!$bucket->exists()) {
            $this->error("Bucket '{$bucketName}' does not exist or is inaccessible.");
            return 1;
        }

        $this->info("Setting CORS policy for bucket: {$bucketName} to origin: {$origin}...");

        $cors = [
            [
                'origin' => [$origin],
                'method' => ['PUT', 'GET', 'HEAD', 'OPTIONS'],
                'responseHeader' => ['Content-Type', 'x-goog-resumable'],
                'maxAgeSeconds' => 3600
            ]
        ];

        $bucket->update([
            'cors' => $cors
        ]);

        $this->info("CORS policy successfully applied to bucket '{$bucketName}'!");
        $this->comment("Policy applied:");
        $this->line(json_encode($cors, JSON_PRETTY_PRINT));
        return 0;
    } catch (\Exception $e) {
        $this->error("Error setting CORS policy: " . $e->getMessage());
        return 1;
    }
})->purpose('Setup CORS policy on the configured Google Cloud Storage bucket');
