<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Events\NewProductArrived;
use App\Models\Product;

class PanRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:pan-run {productFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Product Arrival Notification (PAN) command to handle new products ingested from NWWS-OI client';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $productFile = $this->argument('productFile');
        // Check product file name to see if it matches file save regex
        if (!preg_match('/' . config('nwwsoi-controller.nwwsoi.file_save_regex') . '/', basename($productFile))) {
            return 0;
        }
        // File save regex matches, continue to Inventory product
        try {
            $product = Product::create([
                'name' => basename($productFile),
            ]);
            // Broadcast new product arrived event
            broadcast(new NewProductArrived($product))->via('pusher');
        } catch (QueryException $e) {
            print "Error: Could not inventory product: " . $e->getMessage() . "\n";
            return 1;
        }
        // Send product to all enabled PAN plugins
        if (!is_null(config('nwwsoi-controller.enabled_pan_plugins'))) {
            foreach (explode(',', config('nwwsoi-controller.enabled_pan_plugins')) as $panPlugin) {
                $exitCode = Artisan::call($panPlugin, [
                    'productFile' => $productFile,
                ]);
                if ($exitCode !== 0) {
                    print "There was an error calling $panPlugin.\n";
                }
            }
        }
        // Done!
        return 0;
    }
}
