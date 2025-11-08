<x-filament-panels::page>
    <div class="space-y-6" wire:poll.2s="loadProgress">
        @if($progress)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">
                            Status: 
                            <span class="
                                @if($progress['status'] === 'running') text-yellow-600
                                @elseif($progress['status'] === 'completed') text-green-600
                                @elseif($progress['status'] === 'failed') text-red-600
                                @else text-gray-600
                                @endif
                            ">
                                {{ ucfirst($progress['status']) }}
                            </span>
                        </h3>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $progress['message'] ?? 'No message' }}
                    </div>

                    @if(isset($progress['percentage']))
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span>Progress</span>
                                <span class="font-semibold">{{ $progress['percentage'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                <div 
                                    class="bg-primary-600 h-4 rounded-full transition-all duration-500 ease-out"
                                    style="width: {{ $progress['percentage'] }}%"
                                ></div>
                            </div>
                        </div>
                    @endif

                    @if(isset($progress['total']))
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4">
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($progress['total']) }}
                                </div>
                            </div>

                            @if(isset($progress['processed']))
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <div class="text-xs text-blue-600 dark:text-blue-400 uppercase">Processed</div>
                                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                        {{ number_format($progress['processed']) }}
                                    </div>
                                </div>
                            @endif

                            @if(isset($progress['updated']))
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                    <div class="text-xs text-green-600 dark:text-green-400 uppercase">Updated</div>
                                    <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                                        {{ number_format($progress['updated']) }}
                                    </div>
                                </div>
                            @endif

                            @if(isset($progress['skipped']))
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                                    <div class="text-xs text-yellow-600 dark:text-yellow-400 uppercase">Skipped</div>
                                    <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                                        {{ number_format($progress['skipped']) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center">
                <div class="text-gray-500 dark:text-gray-400">
                    No update in progress. Click "Start Update" to begin.
                </div>
            </div>
        @endif

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        About this process
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p>This process will update all records in the post_nummer table with data from the sweden table, including post_ort and post_lan fields. The progress updates automatically every 2 seconds.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
