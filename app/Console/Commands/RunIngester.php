<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class RunIngester extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:daemon:run-ingester';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the NWWS-OI controller ingester';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Database column to environment variable mapping
        $configVars = [
            'nwwsoi-controller.nwwsoi.host'            => 'NWWSOI_SERVER_HOST',
            'nwwsoi-controller.nwwsoi.port'            => 'NWWSOI_SERVER_PORT',
            'nwwsoi-controller.nwwsoi.username'        => 'NWWSOI_USERNAME',
            'nwwsoi-controller.nwwsoi.password'        => 'NWWSOI_PASSWORD',
            'nwwsoi-controller.nwwsoi.resource'        => 'NWWSOI_RESOURCE',
            'nwwsoi-controller.nwwsoi.archivedir'      => 'NWWSOI_ARCHIVE_DIR',
            'nwwsoi-controller.nwwsoi.pan_run'         => 'NWWSOI_PAN_RUN',
            'nwwsoi-controller.nwwsoi.file_save_regex' => 'NWWSOI_FILE_SAVE_REGEX',
            'nwwsoi-controller.nwwsoi.retry'           => 'NWWSOI_SERVER_CONNECT_RETRY',
        ];
        // Change directory to the base path
        chdir(base_path());
        // Set environment variables for config
        $procEnvVars = [];
        foreach ($configVars as $configVar => $envVar) {
            if (!is_null(config($configVar))) {
                if (preg_match('/\.archivedir$/', $configVar)) {
                    $procEnvVars[$envVar] = storage_path(config($configVar));
                } elseif (preg_match('/\.pan_run$/', $configVar)) {
                    $procEnvVars[$envVar] = base_path(config($configVar));
                } else {
                    $procEnvVars[$envVar] = config($configVar);
                }
            }
        }
        // Set the WxIngest Base URL env var
        $procEnvVars['WXINGEST_BASE_URL'] = config('nwwsoi-controller.wxingest.base_url');
        //Log::debug(json_encode($procEnvVars));
        // Process is not already running, attempt to start it and append output to log
        if (config('nwwsoi-controller.python_client_path') !== '') {
            Log::info("Running command '" . config('nwwsoi-controller.python_client_path') . "'..");
            $process = Process::forever()
                ->env($procEnvVars)
                ->start(config('nwwsoi-controller.python_client_path'));
            // Perform tasks while process is running
            while ($process->running()) {
                //Log::info($process->latestOutput());
                //Log::error($process->latestErrorOutput());
                sleep(1);
            }
            // Wait for process to end (crash or receive TERM/KILL signal)
            $result = $process->wait();
            Log::info('NWWS-OI Ingester process stopped.');
        } else {
            Log::error('Python client path is not defined, exiting.');
        }
        // Done!
        return 0;
    }
}
