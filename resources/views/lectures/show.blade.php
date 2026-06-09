@extends('layouts.app')

@section('page_title', 'Video Lecture')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Back to Course Link -->
    <div>
        <a href="{{ route('courses.show', $course->id) }}" class="text-xs font-bold text-slate-400 hover:text-slate-200 transition-colors flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Syllabus</span>
        </a>
    </div>

    <!-- Main Player Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Video Player Window -->
        <div class="lg:col-span-2 space-y-4">
            
            <div class="bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative aspect-video flex flex-col justify-center items-center bg-black">
                
                <!-- Loading State overlay -->
                <div id="video-loader" class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center space-y-4 z-20 transition-opacity duration-300">
                    <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-brand-500"></div>
                    <p class="text-xs text-slate-400 font-medium tracking-wide">Requesting signed URL from Google Cloud...</p>
                </div>

                <!-- Error State overlay -->
                <div id="video-error" class="absolute inset-0 bg-slate-950/90 hidden flex-col items-center justify-center space-y-3 z-30 p-6 text-center">
                    <svg class="w-10 h-10 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h4 class="text-sm font-bold text-slate-200">Failed to Retrieve Signed URL</h4>
                    <p id="error-message" class="text-xs text-slate-500 max-w-xs"></p>
                    <button onclick="requestVideoUrl()" class="mt-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all duration-200">
                        Retry Fetch
                    </button>
                </div>

                <!-- HTML5 Video Player -->
                <video id="lms-video-player" controls controlsList="nodownload" oncontextmenu="return false;"
                    class="h-full w-full object-contain z-10 hidden">
                    Your browser does not support the video tag.
                </video>

            </div>

            <!-- Lecture Title & Info -->
            <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl shadow-lg">
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-brand-500/10 text-brand-400 border border-brand-500/20">Video Lecture</span>
                    <span class="text-[10px] text-slate-500">•</span>
                    <span class="text-[10px] text-slate-500">Course: {{ $course->title }}</span>
                </div>
                <h3 class="text-lg font-bold text-slate-100 mt-2">{{ $lecture->title }}</h3>
                <p class="text-xs text-slate-400 mt-1">Uploaded securely to: <code class="text-[10px] bg-slate-900 border border-slate-800 px-1.5 py-0.5 rounded text-indigo-400">{{ $lecture->file_path }}</code></p>
                <div class="mt-4 pt-4 border-t border-slate-900 text-xs text-slate-500 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Secure temporary streaming connection active. Expiring in 20 minutes.</span>
                </div>
            </div>

        </div>

        <!-- Navigation Syllabus Side Panel -->
        <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col h-full max-h-[480px]">
            <h3 class="text-sm font-bold text-slate-200 mb-1">Course Navigation</h3>
            <p class="text-[10px] text-slate-500 mb-4">Syllabus lectures catalog</p>

            <div class="space-y-4 overflow-y-auto pr-1 flex-1">
                @foreach($course->modules as $mod)
                    <div class="space-y-1.5">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider truncate">{{ $mod->title }}</h4>
                        <div class="space-y-1">
                            @foreach($mod->lectures as $lect)
                                <a href="{{ route('lectures.show', $lect->id) }}"
                                    class="flex items-center space-x-2 px-3 py-2 rounded-xl text-xs transition-all duration-200 
                                    {{ $lect->id === $lecture->id ? 'bg-brand-600/20 text-brand-400 border border-brand-500/30 font-semibold' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200 border border-transparent' }}">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    </svg>
                                    <span class="truncate">{{ $lect->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

<!-- Secure Signed URL API Fetcher -->
<script>
    const streamApiUrl = "{{ route('lectures.stream', $lecture->id) }}";
    const loader = document.getElementById('video-loader');
    const player = document.getElementById('lms-video-player');
    const errorOverlay = document.getElementById('video-error');
    const errorMessage = document.getElementById('error-message');

    function requestVideoUrl() {
        // Show loading state
        loader.classList.remove('hidden');
        errorOverlay.classList.add('hidden');
        player.classList.add('hidden');

        fetch(streamApiUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server returned error status ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.video_url) {
                // Populate URL and display player
                player.src = data.video_url;
                loader.classList.add('hidden');
                player.classList.remove('hidden');
                player.play().catch(e => {
                    console.log("Auto-play blocked by browser. User interaction required.");
                });
            } else if (data.error) {
                throw new Error(data.error);
            } else {
                throw new Error('Invalid JSON response schema.');
            }
        })
        .catch(error => {
            console.error('Error fetching signed stream url:', error);
            loader.classList.add('hidden');
            errorMessage.textContent = error.message || 'An unknown network error occurred.';
            errorOverlay.classList.remove('hidden');
        });
    }

    // Trigger URL request on load
    document.addEventListener('DOMContentLoaded', requestVideoUrl);
</script>
@endsection
