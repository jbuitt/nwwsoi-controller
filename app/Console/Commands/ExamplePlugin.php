<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExamplePlugin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:pan-plugin:example-plugin {productFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plugin example';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the product filename
        $productFile = $this->argument('productFile');
        // Do something with product...

        // Done
        return 0;
    }
}
