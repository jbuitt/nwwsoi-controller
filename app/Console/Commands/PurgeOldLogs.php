<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:purge_old_logs {days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old log files in storage/logs/.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Get input
        $days = $this->argument('days');
        // Make sure input is valid
        if (!is_numeric($days) && $days > 0) {
            print "Error: Supplied number of days '$days' is invalid. Should be a number greater than 0.\n";
            return 1;
        }
        // Delete old files
        Log::info('Purging old log files older than ' . $days . ' days..');
        exec('/usr/bin/find ' . storage_path('logs/') . ' -type f -mtime +' . $days . ' -delete 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            print "Error: " . implode("\n", $output) . "\n";
        }
        // Done
        print "Done!\n";
        return $exitCode;
    }
}
