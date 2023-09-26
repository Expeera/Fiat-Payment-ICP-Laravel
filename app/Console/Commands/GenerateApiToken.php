<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class GenerateApiToken extends Command
{
    protected $signature = 'generate:token';
    protected $description = 'Generate and update API token in .env file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $apiToken = Str::random(64);

        $envFilePath = base_path('.env');

        if (File::exists($envFilePath)) {

            $envFileContents = File::get($envFilePath);

            Artisan::call("config:clear");

            if (!env('API_TOKEN', null)) {
                $envFileContents .= PHP_EOL . "API_TOKEN=" . $apiToken;
            } else {
                $envFileContents = preg_replace('/API_TOKEN=.*/', 'API_TOKEN=' . $apiToken, $envFileContents);
            }

            File::put($envFilePath, $envFileContents);

            $this->info('API token has been generated and updated in .env file.');
        } else {
            $this->error('.env file not found.');
        }
    }
}
