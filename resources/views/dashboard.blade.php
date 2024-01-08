<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight grid grid-cols-2">
            <span>{{ config('app.name') . ' Dashboard' }}</span>
            <span id="dateTimeSpan" class="text-right text-black dark:text-white w-full font-mono">Loading..</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <livewire:process-control-panel />
                <div class="p-6">
                    <livewire:product-table />
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener("load", function() {
            window.setInterval(function() {
                document.getElementById('dateTimeSpan').textContent = window.moment.utc().format('YYYY-MM-DD HH:mm:ss');
            }, 1000);
        });
    </script>

</x-app-layout>
