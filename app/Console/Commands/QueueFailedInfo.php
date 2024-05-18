<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\FailedJob;

class QueueFailedInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:failed-info {uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets the information about a failed queue job';

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
     * @return int
     */
    public function handle()
    {
        $uuid = $this->argument('uuid');
        try {
            $failedJob = FailedJob::where('uuid', '=', $uuid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            print "Could not find job with ID $uuid.\n";
        }
        print $this->info('UUID:') . $failedJob->uuid . "\n\n";
        print $this->info('Connection:') . $failedJob->queue . "\n\n";
        print $this->info('Queue:') . $failedJob->queue . "\n\n";
        print $this->info('Payload:') . $failedJob->payload . "\n\n";
        print $this->info('Exception:') . $failedJob->exception . "\n\n";
        print $this->info('Failed At:') . $failedJob->failed_at . "\n\n";
        return 0;
    }
}
