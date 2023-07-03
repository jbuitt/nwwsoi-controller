<div class="p-6" style="color:#fff;">
    <button id="controlButton" wire:click="toggleProcess" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        <i class="fa-solid {{ $buttonIconClass }}"></i>
        <span id="controlButton" class="px-2">{{ $buttonLabel }}</span>
    </button>
    <span class="px-4 float-right">Process Status: <b>{{ $processStatus }}</b></span>
    <script>
        document.getElementById('controlButton').addEventListener('click', function(e) {
            let buttonText = document.getElementById('controlButton').innerText;
            let actionText = 'Starting';
            if (buttonText == ' Stop') {
                actionText = 'Stopping';
            }
            console.log(`buttonText = ${buttonText}`);
            Swal.fire({
                html: `<h5><i class="fa-solid fa-spinner fa-spin-pulse"></i> ${actionText}...</h5>`,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            });
        });
        window.addEventListener('commandFinished', () => {
            Swal.close();
        });
    </script>
</div>
