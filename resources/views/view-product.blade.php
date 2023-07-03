<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('View Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray dark:bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="whitespace-pre">{{ file_get_contents(storage_path(config('nwwsoi-controller.nwwsoi.archivedir')) . '/' . substr($product->name, 0, 4) . '/' . $product->name) }}</div>
            </div>
        </div>
    </div>

</x-app-layout>
