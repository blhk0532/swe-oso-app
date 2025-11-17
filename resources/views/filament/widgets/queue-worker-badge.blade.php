@props(['running'])

<div class="mi-flex mi-items-center mi-gap-2">
    @if($running)
        <span class="mi-inline-block mi-w-2 mi-h-2 mi-rounded-full mi-bg-green-500" aria-hidden></span>
        <span>Filament worker running</span>
    @else
        <span class="mi-inline-block mi-w-2 mi-h-2 mi-rounded-full mi-bg-yellow-500" aria-hidden></span>
        <span>Filament worker not running</span>
    @endif
</div>
