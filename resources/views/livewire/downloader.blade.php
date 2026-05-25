<div @if($shouldPoll) wire:poll.2s="checkStatus" @endif class="w-full max-w-4xl mx-auto relative min-h-[300px]">
    
    {{-- Error Banner --}}
    @if ($status === 'failed' && $errorMessage)
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl backdrop-blur-xl animate-fade-in">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-red-500/20 rounded-xl mt-0.5 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-red-400 font-semibold text-sm sm:text-base">An Error Occurred</h4>
                    <p class="text-red-300/70 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ $errorMessage }}
                    </p>
                </div>
                <button wire:click="resetDownloader" class="text-red-400/50 hover:text-red-400 p-1 rounded-lg hover:bg-white/5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- State: URL Input Form (Idle) --}}
    @if ($status === 'idle' || $status === 'failed')
        <div wire:key="state-idle" class="w-full">
            <form wire:submit.prevent="submit" class="space-y-4" autocomplete="off">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 via-cyan-400 to-indigo-500 rounded-2xl blur opacity-35 group-hover:opacity-60 transition duration-500"></div>
                    <div class="relative flex flex-col sm:flex-row items-stretch sm:items-center bg-slate-900/90 backdrop-blur-2xl rounded-2xl border border-white/10 p-2 gap-2">
                        <div class="flex items-center flex-1 pl-4">
                            <div class="text-blue-400 shrink-0 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <input wire:model="url" type="url" id="video-url-input"
                                placeholder="Paste your video or audio link here..."
                                class="w-full bg-transparent text-white placeholder-slate-400 text-sm sm:text-base outline-none py-3 pr-2"
                                autocomplete="off" required />
                        </div>
                        
                        <button type="submit" id="download-submit-btn" wire:loading.attr="disabled"
                            class="px-8 py-3.5 sm:py-4 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-bold rounded-xl text-sm sm:text-base transition-all duration-300 shadow-lg shadow-blue-500/25 hover:shadow-cyan-500/40 hover:scale-[1.01] active:scale-98 flex items-center justify-center gap-2 whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Analyze Link</span>
                        </button>
                    </div>
                </div>
                
                @error('url')
                    <p class="text-red-400 text-xs sm:text-sm mt-2 flex items-center gap-1.5 pl-2 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </form>
        </div>
    @endif

    {{-- State: Fetching Metadata / Analyzing --}}
    @if ($status === 'fetching_metadata')
        <div wire:key="state-analyzing" class="text-center py-12 px-6">
            <div class="relative max-w-sm mx-auto">
                <div class="absolute -inset-1.5 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-3xl blur opacity-25 animate-pulse"></div>
                <div class="relative bg-slate-900/90 backdrop-blur-2xl rounded-3xl border border-white/10 p-8 sm:p-10 text-center">
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="w-18 h-18 border-4 border-slate-800 rounded-full"></div>
                            <div class="w-18 h-18 border-4 border-transparent border-t-cyan-400 border-r-blue-500 rounded-full animate-spin absolute inset-0"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-cyan-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-white font-bold text-lg sm:text-xl mb-2 animate-pulse">Analyzing Video URL...</h3>
                    <p class="text-slate-400 text-xs sm:text-sm">Fetching titles, thumbnails, and compiling all available high-definition qualities for you.</p>
                    <div class="mt-6 h-1 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full w-2/3 animate-[progress_1.5s_ease-in-out_infinite]"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- State: Metadata Fetched & Quality Selector --}}
    @if ($status === 'metadata_fetched')
        <div wire:key="state-selector" class="w-full bg-slate-900/60 backdrop-blur-2xl rounded-3xl border border-white/10 p-6 sm:p-8 animate-fade-in-up">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                
                {{-- Video Card Preview (4 cols) --}}
                <div class="md:col-span-5 space-y-4">
                    <div class="relative group aspect-video md:aspect-square overflow-hidden rounded-2xl border border-white/10 bg-black">
                        @if ($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        
                        {{-- Video Duration Badge --}}
                        @if ($duration)
                            <div class="absolute bottom-3 right-3 bg-black/85 px-2.5 py-1 rounded-md text-xs font-mono font-bold text-white tracking-wide border border-white/5">
                                {{ $this->getFormattedDuration() }}
                            </div>
                        @endif
                        
                        {{-- Channel Badge --}}
                        @if ($uploader)
                            <div class="absolute top-3 left-3 bg-slate-900/85 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium text-blue-400 border border-white/5 shadow-lg">
                                {{ $uploader }}
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <h2 class="text-white font-bold text-lg sm:text-xl leading-snug line-clamp-2" title="{{ $title }}">{{ $title }}</h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-slate-400 text-xs sm:text-sm">Platform:</span>
                            <span class="text-xs font-bold uppercase px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                {{ parse_url($url, PHP_URL_HOST) ? str_replace('www.', '', parse_url($url, PHP_URL_HOST)) : 'Video' }}
                            </span>
                        </div>
                    </div>
                    
                    <button wire:click="resetDownloader" class="w-full py-3 bg-slate-800/60 hover:bg-slate-800 text-slate-300 hover:text-white font-medium rounded-xl text-xs sm:text-sm transition duration-200 border border-white/5 flex items-center justify-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Analyze Another Link</span>
                    </button>
                </div>
                
                {{-- Quality Options List (7 cols) --}}
                <div class="md:col-span-7 space-y-6">
                    <div>
                        <h3 class="text-white font-bold text-base sm:text-lg border-b border-white/10 pb-2 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Video Options (MP4)</span>
                        </h3>
                        
                        <div class="space-y-2 max-h-[280px] overflow-y-auto pr-1">
                            @php $hasVideo = false; @endphp
                            @foreach ($formats as $f)
                                @if (($f['type'] ?? '') === 'video')
                                    @php $hasVideo = true; @endphp
                                    <div class="flex items-center justify-between p-3.5 bg-slate-800/40 hover:bg-slate-800/70 border border-white/5 rounded-xl transition duration-200">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-black px-2.5 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded">
                                                {{ strtoupper($f['ext'] ?? 'mp4') }}
                                            </span>
                                            <div>
                                                <p class="text-white font-bold text-sm sm:text-base">{{ $f['quality'] }}</p>
                                                <p class="text-slate-400 text-xs">Estimated size: <span class="text-slate-300 font-medium">{{ $f['size'] }}</span></p>
                                            </div>
                                        </div>
                                        <button wire:click="startDownload('{{ $f['id'] }}', '{{ $f['quality'] }}')" 
                                            class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm rounded-lg shadow transition-all hover:scale-[1.03] active:scale-97">
                                            Download
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                            
                            @if(!$hasVideo)
                                <p class="text-slate-500 text-xs italic">No direct video streams found.</p>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-white font-bold text-base sm:text-lg border-b border-white/10 pb-2 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                            <span>Audio Only Options</span>
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($formats as $f)
                                @if (($f['type'] ?? '') === 'audio')
                                    <div class="flex flex-col justify-between p-4 bg-slate-800/40 hover:bg-slate-800/70 border border-white/5 rounded-xl transition duration-200 gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-black px-2.5 py-1 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded">
                                                {{ strtoupper($f['ext'] ?? 'mp3') }}
                                            </span>
                                            <div>
                                                <p class="text-white font-bold text-xs sm:text-sm">{{ $f['quality'] }}</p>
                                                <p class="text-slate-400 text-xxs sm:text-xs">Est. Size: {{ $f['size'] }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="startDownload('{{ $f['id'] }}', '{{ $f['quality'] }}')" 
                                            class="w-full py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-lg shadow transition-all hover:scale-[1.02] active:scale-97">
                                            Extract Audio
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- State: Downloading / Processing --}}
    @if ($status === 'processing')
        <div wire:key="state-downloading" class="text-center py-12 px-6">
            <div class="relative max-w-md mx-auto">
                <div class="absolute -inset-1.5 bg-gradient-to-r from-blue-600 to-indigo-500 rounded-3xl blur opacity-25 animate-pulse"></div>
                <div class="relative bg-slate-900/90 backdrop-blur-2xl rounded-3xl border border-white/10 p-8 sm:p-10 text-center">
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="w-18 h-18 border-4 border-slate-800 rounded-full animate-ping opacity-20"></div>
                            <div class="w-18 h-18 border-4 border-transparent border-t-blue-500 border-l-cyan-400 rounded-full animate-spin absolute inset-0"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="text-white font-bold text-lg sm:text-xl mb-2">Downloading & Processing...</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        We are downloading your requested format and merging video & audio tracks or encoding audio using FFMPEG.
                    </p>
                    
                    <div class="mt-4 p-3 bg-white/5 border border-white/5 rounded-xl flex items-center justify-center gap-2">
                        <span class="text-slate-400 text-xs">Quality selected:</span>
                        <span class="text-xs font-bold text-blue-400">{{ $formats[array_search($downloadId, array_column($formats, 'id'))]['quality'] ?? 'Requested Quality' }}</span>
                    </div>

                    <div class="mt-6 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 via-cyan-400 to-indigo-500 rounded-full w-full animate-progress-bar"></div>
                    </div>
                    <p class="text-slate-500 text-xxs sm:text-xs mt-3 flex items-center justify-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Please don't close this window. Your download is being prepared.</span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- State: Completed --}}
    @if ($status === 'completed')
        <div wire:key="state-completed" class="text-center py-8 px-6">
            <div class="relative max-w-lg mx-auto">
                <div class="absolute -inset-1.5 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-3xl blur opacity-20 animate-pulse"></div>
                <div class="relative bg-slate-900/90 backdrop-blur-2xl rounded-3xl border border-white/10 p-8 sm:p-10">
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/30 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="absolute -inset-2 border-2 border-emerald-500/20 rounded-full animate-ping-slow"></div>
                        </div>
                    </div>

                    <h3 class="text-white font-black text-xl sm:text-2xl mb-1">Your Media is Ready!</h3>
                    <p class="text-slate-400 text-xs sm:text-sm mb-6">Your high-quality file has been successfully processed and is ready to save.</p>
                    
                    {{-- Mini preview info --}}
                    <div class="mb-6 p-4 bg-white/5 border border-white/5 rounded-2xl flex items-center gap-4 text-left">
                        @if ($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $title }}" class="w-16 h-16 object-cover rounded-lg shrink-0 border border-white/10" />
                        @endif
                        <div class="min-w-0">
                            <h4 class="text-white font-bold text-sm truncate" title="{{ $title }}">{{ $title }}</h4>
                            <p class="text-slate-400 text-xs truncate mt-0.5">Uploader: {{ $uploader }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('download.serve', $downloadId) }}" id="download-file-btn"
                            class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-extrabold rounded-xl text-sm sm:text-base transition-all duration-300 shadow-lg shadow-blue-500/25 hover:shadow-cyan-500/40 hover:scale-[1.02] active:scale-98 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Save to Device</span>
                        </a>
                        <button wire:click="resetDownloader" type="button" id="download-another-btn"
                            class="inline-flex items-center justify-center gap-2 px-6 py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold rounded-xl text-sm sm:text-base transition-all duration-300 border border-white/5 hover:border-white/10 active:scale-98">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.706 8.9H19.5" />
                            </svg>
                            <span>Download Another</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
