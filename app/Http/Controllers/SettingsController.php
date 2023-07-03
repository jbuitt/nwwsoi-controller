<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Product;

class SettingsController extends Controller
{
    /**
     * Display the app settings form.
     */
    public function edit(Request $request): View
    {
        return view('settings.edit');
    }

    /**
     * Update the app's settings.
     */
    public function update(Request $request): RedirectResponse
    {
        // $request->user()->fill($request->validated());

        // If request to purge all products, do that noew
        if ($request->input('type') === 'purge_all_products') {
            Product::truncate();
            chdir(storage_path(config('nwwsoi-controller.nwwsoi.archivedir')));
            exec('rm -rf *');
            return Redirect::route('settings.edit')->with('status', 'All products have been deleted!');
        }
        return Redirect::route('settings.edit')->with('status', 'settings-updated');
    }

}
