@extends('layouts.app')

@section('page_title', 'Teacher Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Courses Created</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['courses_count'] }}</h3>
            </div>
            <div class="p-3 bg-brand-500/10 text-brand-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lectures Uploaded</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['lectures_count'] }}</h3>
            </div>
            <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Upcoming Live Classes</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['upcoming_classes_count'] }}</h3>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Content Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Courses List -->
        <div class="xl:col-span-2 bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-100">My Courses</h3>
                    <p class="text-xs text-slate-400 mt-1">Manage and edit your course curricula</p>
                </div>
                <a href="{{ route('courses.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-colors shadow-md shadow-brand-600/10">
                    + New Course
                </a>
            </div>

            <div class="space-y-4">
                @forelse($courses as $course)
                <div class="p-5 bg-slate-900/50 border border-slate-800/80 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between hover:border-slate-750 transition-colors gap-4">
                    <div class="min-w-0 flex items-center space-x-4">
                        <div class="h-12 w-12 rounded-xl bg-slate-850 flex-shrink-0 flex items-center justify-center overflow-hidden border border-slate-800">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="h-full w-full object-cover">
                            @else
                                <svg class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-200 truncate">{{ $course->title }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5 max-w-sm truncate">{{ $course->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2.5">
                        <form action="{{ route('courses.toggle-publish', $course->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-800 hover:bg-slate-850 text-xs font-semibold text-slate-300 transition-all duration-200">
                                {{ $course->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                        <a href="{{ route('courses.show', $course->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-750 text-xs font-semibold text-slate-200 transition-all duration-200">
                            View
                        </a>
                        <a href="{{ route('courses.edit', $course->id) }}" class="px-3 py-1.5 rounded-xl bg-brand-600/10 hover:bg-brand-600/20 text-xs font-semibold text-brand-400 border border-brand-500/20 transition-all duration-200">
                            Edit
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-slate-500 border border-dashed border-slate-850 rounded-2xl bg-slate-900/10">
                    <p class="text-sm font-medium">No courses created yet.</p>
                    <p class="text-xs text-slate-600 mt-1">Get started by creating your first curriculum above.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Live Classes -->
        <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col">
            <h3 class="text-lg font-bold text-slate-100 mb-1">Upcoming Live Classes</h3>
            <p class="text-xs text-slate-400 mb-6">Schedule links for your enrolled students</p>

            <div class="space-y-4 overflow-y-auto max-h-[350px] flex-1">
                @forelse($upcomingClasses as $class)
                <div class="p-4 bg-slate-900/50 border border-slate-850 rounded-2xl space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="min-w-0">
                            <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider">Live Session</span>
                            <h4 class="text-sm font-bold text-slate-200 truncate mt-0.5">{{ $class->title }}</h4>
                        </div>
                        <form action="{{ route('live-classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Cancel this live class?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-500 hover:text-rose-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    <div class="flex justify-between items-center text-xs text-slate-400">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $class->datetime->format('M d, H:i') }}</span>
                        </div>
                        <a href="{{ $class->link }}" target="_blank" class="px-2.5 py-1 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 font-bold hover:bg-indigo-500/30 transition-all duration-200">
                            Join Link
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-xs text-slate-500">No upcoming classes scheduled.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
