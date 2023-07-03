<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Traits\DaemonTrait;

class StartupProcesses extends Command
{
    use DaemonTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:startup:processes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute startup processes';

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
        // Check for valid config
        $response = $this->checkConfig();
        if ($response['statusCode'] !== 200) {
            print $response['details'] . "\n";
            return 1;
        }
        // If autostart is enabled, then start up the daemon
        if (intval(config('nwwsoi-controller.nwwsoi.autostart'))) {
            // Execute command
            print "Auto-starting config..\n";
            print json_encode($this->executeArtisanCommand('start')) . "\n";
        } else {
            print "There is a config present, but autostart is set to false.\n";
        }
        // Sleep for 60 seconds so supervisord doesn't think the process stopped too quickly
        print "Sleeping for 60 seconds..\n";
        sleep(60);
        // Done
        return 0;
    }

}
