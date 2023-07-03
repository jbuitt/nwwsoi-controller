<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Purge old products from filesystem.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function purgeOldProds(Request $request, $days)
    {
        // Check request input
        if (!is_numeric($days) && $days > 0) {
            return response()->json([
                'statusCode' => 400,
                'message' => 'Bad Request',
                'details' => 'Days should be numeric and greater than 0.',
            ], 400);
        }
        // Call console command to delete old products
        $exitCode = Artisan::call('nwwsoi-controller:purge_old_products', ['days' => $days]);
        // Return response
        if ($exitCode === 0) {
            return response()->json([
                'statusCode' => 200,
                'message' => 'OK',
                'details' => 'Products older than ' . $days . ' days deleted (Exit code: ' . $exitCode . ')',
            ], 200);
        } else {
            return response()->json([
                'statusCode' => 500,
                'message' => 'Internal Server Error',
                'details' => 'Error purging old products (Exit code: ' . $exitCode . ')',
            ], 500);
        }
    }

}
