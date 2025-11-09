<script>
    document.addEventListener('alpine:init', () => {
        if (typeof window.Echo === 'undefined') {
            // Echo not loaded; ensure you have broadcasting configured (pusher/ably/reverb)
            return;
        }

        window.Echo.channel('postnummer.status')
            .listen('.PostNummerStatusUpdated', (e) => {
                // Try to refresh any Livewire table on the page
                const components = window.Livewire?.all();
                if (components) {
                    components.forEach((c) => {
                        // Gentle refresh if component exposes refresh
                        if (typeof c?.$refresh === 'function') {
                            c.$refresh();
                        }
                    });
                }
            });
    });
</script>
