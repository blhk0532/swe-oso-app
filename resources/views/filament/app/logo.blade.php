
	<img
		src="{{ asset('logo.svg') }}"
		alt="Fireflow logo"
		class="h-full w-auto brand-logo"
	>
	<span style="padding-left: 20px;
    font-family: monospace;
    letter-spacing: 2px;
    font-size: medium;" class="ml-3 hidden sm:inline text-sm md:text-base font-semibold tracking-tight text-gray-900 dark:text-gray-100">
		fireflow.se
	</span>


<style>
/* Subtle breathing glow + float animation */
@keyframes brandPulse {
	0% { transform: translateY(0) scale(1); filter: drop-shadow(0 0 0 rgba(255, 85, 0, 0)); }
	50% { transform: translateY(-1px) scale(1.02); filter: drop-shadow(0 0 6px rgba(255, 85, 0, 0.35)); }
	100% { transform: translateY(0) scale(1); filter: drop-shadow(0 0 0 rgba(255, 85, 0, 0)); }
}

.brand-logo {
	animation: brandPulse 2.8s ease-in-out infinite;
	will-change: transform, filter;
}

@media (prefers-reduced-motion: reduce) {
	.brand-logo { animation: none; }
}
</style>
