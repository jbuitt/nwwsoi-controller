<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateNwwsLogFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:update_nwws_log_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates log file that nwws.py uses to write logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Check for nwws.py PID file
        if (!file_exists(storage_path('logs') . 'nwws.pid')) {
            $pid = file_get_contents(storage_path('logs/') . 'nwws.pid');
            // Check if the process is actually running
            if (!file_exists('/proc/' . $pid)) {
                print "The nwws.py process is not running, removing pid file and existing.\n";
                @unlink(storage_path('logs/') . 'nwws.pid');
                return 0;
            }
        } else {
            print "The nwws.pid file does not exist, exiting.\n";
            return 0;
        }
        // Send USR1 signal to process to update log file
        exec('/usr/bin/kill -USR1 ' . $pid . ' 2>&1', $output, $retval);
        // Done!
        return $retval;
    }
}
