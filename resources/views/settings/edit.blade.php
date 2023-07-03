<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-black dark:text-white bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg py-4">
                    @if (session('status'))
                    <div class="alert alert-success py-4">
                        {{ session('status') }}
                    </div>
                    @endif
                    <div>
                        If you would like to remove all products from database and filesystem, click button below.
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.update') }}">
                    <input type="HIDDEN" name="type" value="purge_all_products" />
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        <span id="controlButton" class="px-2">Purge all products</span>
                    </button>
                </form>
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
            </div>
        </div>
    </div>
</x-app-layout>
