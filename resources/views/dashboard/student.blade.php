@extends('layouts.app')

@section('page_title', 'Student Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Enrolled Courses</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['enrolled_count'] }}</h3>
            </div>
            <div class="p-3 bg-brand-500/10 text-brand-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
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

    <!-- Main Student Panels -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Enrolled Courses -->
        <div class="xl:col-span-2 bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-100">My Enrolled Courses</h3>
                    <p class="text-xs text-slate-400 mt-1">Jump back into your active curriculum</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-xs font-bold text-brand-400 hover:text-brand-300 transition-colors">Course Catalog &rarr;</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($enrolledCourses as $course)
                <div class="bg-slate-900/50 border border-slate-850 hover:border-slate-800 p-5 rounded-2xl flex flex-col justify-between transition-all duration-200">
                    <div>
                        <div class="h-28 w-full rounded-xl bg-slate-850 overflow-hidden mb-4 border border-slate-800">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center text-slate-600 font-bold bg-slate-900">
                                    No Image
                                </div>
                            @endif
                        </div>
                        <h4 class="text-sm font-bold text-slate-200 truncate">{{ $course->title }}</h4>
                        <p class="text-[10px] text-slate-400 mt-1">Instructor: {{ $course->teacher->name }}</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <form action="{{ route('enrollments.unenroll', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to unenroll from this course?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-semibold transition-colors">
                                Unenroll
                            </button>
                        </form>
                        <a href="{{ route('courses.show', $course->id) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                            Open Syllabus
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-2 text-center py-12 text-slate-500 border border-dashed border-slate-850 rounded-2xl bg-slate-900/10">
                    <p class="text-sm font-medium">You are not enrolled in any courses yet.</p>
                    <a href="{{ route('courses.index') }}" class="inline-block mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs transition-colors">
                        Browse Course Catalog
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Live Classes -->
        <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col">
            <h3 class="text-lg font-bold text-slate-100 mb-1">Upcoming Live Classes</h3>
            <p class="text-xs text-slate-400 mb-6">Join scheduled Meet sessions with instructors</p>

            <div class="space-y-4 overflow-y-auto max-h-[350px] flex-1">
                @forelse($upcomingClasses as $class)
                <div class="p-4 bg-slate-900/50 border border-slate-850 rounded-2xl space-y-3 hover:border-slate-800 transition-colors">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider">Scheduled Live</span>
                        <h4 class="text-sm font-bold text-slate-200 mt-0.5 truncate">{{ $class->title }}</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5 truncate">Course: {{ $class->course->title }}</p>
                    </div>
                    <div class="flex justify-between items-center text-xs text-slate-400">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $class->datetime->format('M d, H:i') }}</span>
                        </div>
                        <a href="{{ $class->link }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-[10px] transition-all duration-200 shadow-md shadow-brand-600/10">
                            Launch Class
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-xs text-slate-500">No upcoming classes scheduled.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
