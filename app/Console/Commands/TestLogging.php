<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:test_logging {level}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prints out remote logging info and triggers a new log message';

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
        $levels = [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug',
        ];
        $level = $this->argument('level');
        if (!in_array($level, $levels)) {
            print "Error: Invalid level '$level'. Should be one of " . implode(', ', $levels) . "\n";
            return 1;
        }
        print "Logging config:\n\n";
        print "  Host: " . config('nwwsoi-controller.logging.host') . "\n";
        print "  Transport: " . config('nwwsoi-controller.logging.transport') . "\n";
        print "  Port: " . config('nwwsoi-controller.logging.port') . "\n";
        print "\n";
        print "Triggering new log message with level '$level'..  ";
        Log::$level('Test log message.', ['foo' => json_encode(['bar' => 'baz'])]);
        print "Done.\n";
        return 0;
    }
}
