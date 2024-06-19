<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\NwwsProcessRestart;

class NwwsProcessWatchdog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:nwws-process-watchdog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watches the NWWS-OI ingester process and restarts it if the connection is lost';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get the last line of the current nwws.py log file
        $logFile = storage_path('logs') . '/nwws-' . date('Y-m-d') . '.log';
        // Get last line of log file
        $lastLogLine = rtrim(shell_exec('tail -n 1 ' . $logFile));
        // Check last line for connection lost message
        if (preg_match('/connection_lost:/', $lastLogLine)) {
            // Line found, restart NWWS-OI process
            Log::info('Connection lost found, restarting NWWS-OI process..', [
                'app_name' => config('app.name')
            ]);
            Artisan::call('nwwsoi-controller:daemon:control restart');
            // Add an entry to the nwws_process_restarts table
            NwwsProcessRestart::create();
        }
        // Done!
        return 0;
   }

}

