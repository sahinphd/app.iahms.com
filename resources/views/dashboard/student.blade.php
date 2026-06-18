@extends('layouts.app')

@section('page_title', 'Student Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Assigned Class Banner (If student belongs to a class) -->
    @if(Auth::user()->schoolClass)
        <div class="bg-gradient-to-r from-brand-950/30 via-slate-950/40 to-slate-950/20 border border-brand-500/20 p-6 rounded-3xl shadow-lg relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-all duration-300"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-brand-500/10 text-brand-400 rounded-2xl border border-brand-500/20">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-extrabold uppercase tracking-wider bg-brand-500/15 text-brand-400 px-2 py-0.5 rounded border border-brand-500/20">Allotted Batch / Class</span>
                        <h3 class="text-lg font-bold text-slate-100 mt-1 font-Outfit">{{ Auth::user()->schoolClass->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5 max-w-2xl leading-relaxed">{{ Auth::user()->schoolClass->description ?: 'No classroom description available.' }}</p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('classes.show', Auth::user()->schoolClass->id) }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all duration-200 shadow-md shadow-brand-600/10 uppercase tracking-wider">
                        <span>View Class Details & Roster</span>
                        <svg class="w-3.5 h-3.5 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

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
                        <div class="h-28 w-full rounded-xl bg-slate-850 overflow-hidden mb-4 border border-slate-800 relative">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center text-slate-600 font-bold bg-slate-900">
                                    No Image
                                </div>
                            @endif

                            @if($course->is_completed)
                                <span class="absolute top-2 right-2 px-2 py-1 text-[9px] font-bold bg-emerald-950/90 text-emerald-400 border border-emerald-500/30 rounded-xl flex items-center space-x-1 backdrop-blur-sm">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Completed</span>
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-slate-200 truncate">{{ $course->title }}</h4>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Instructor: {{ $course->teacher->name }}</p>
                        @if($course->duration)
                            <p class="text-[10px] text-slate-500 mt-0.5">Duration: {{ $course->duration }}</p>
                        @endif
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        @if($course->school_class_id && $course->school_class_id === Auth::user()->school_class_id)
                            <span class="text-[9px] font-extrabold text-slate-500 uppercase tracking-wider bg-slate-900/60 border border-slate-800 px-2.5 py-1.5 rounded-xl select-none">
                                Class Course
                            </span>
                        @else
                            <form action="{{ route('enrollments.unenroll', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to unenroll from this course?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-semibold transition-colors">
                                    Unenroll
                                </button>
                            </form>
                        @endif
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

        <div class="space-y-6">
            <!-- Class Noticeboard announcements -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col space-y-4">
                <div>
                    <h3 class="text-base font-bold text-slate-200 mb-1">Class Noticeboard</h3>
                    <p class="text-xs text-slate-450">Announcements from your class instructors</p>
                </div>

                <div class="space-y-3 overflow-y-auto max-h-[300px]">
                    @forelse($classNotes as $note)
                        <div class="p-3.5 bg-slate-900/50 border border-slate-850 rounded-2xl space-y-2 hover:border-slate-800 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    @if(!$note->school_class_id)
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-brand-500/10 text-brand-400 border border-brand-500/20">Global Notice</span>
                                    @endif
                                    <h4 class="text-xs font-bold text-slate-200 mt-1">{{ $note->title }}</h4>
                                    <p class="text-[9px] text-slate-500 mt-0.5">By {{ $note->teacher->name }}</p>
                                </div>
                                <span class="text-[9px] text-slate-500">{{ $note->created_at->format('M d, H:i') }}</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">{{ $note->content }}</p>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-slate-550 italic">No announcements posted for your class.</div>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Live Classes -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col">
                <h3 class="text-base font-bold text-slate-200 mb-1">Upcoming Live Classes</h3>
                <p class="text-xs text-slate-450 mb-4">Join scheduled Meet sessions with instructors</p>

                <div class="space-y-4 overflow-y-auto max-h-[300px]">
                    @forelse($upcomingClasses as $class)
                    <div class="p-4 bg-slate-900/50 border border-slate-850 rounded-2xl space-y-3 hover:border-slate-800 transition-colors">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider">Scheduled Live</span>
                            <h4 class="text-sm font-bold text-slate-200 mt-0.5 truncate">{{ $class->title }}</h4>
                            <p class="text-[10px] text-slate-500 mt-0.5 truncate">Subject: {{ $class->subject->title }}</p>
                        </div>
                        <div class="flex justify-between items-center text-xs text-slate-405">
                            <div class="flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $class->datetime->format('M d, H:i') }}</span>
                            </div>
                            <a href="{{ route('live-classes.join', $class->id) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-[10px] transition-all duration-200 shadow-md shadow-brand-600/10">
                                Launch Class
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-xs text-slate-555">No upcoming classes scheduled.</div>
                    @endforelse
                </div>
            </div>

            <!-- Profile Settings Card -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                <div>
                    <h3 class="text-base font-bold text-slate-100 mb-1">My Profile Settings</h3>
                    <p class="text-xs text-slate-400">Update your personal account details</p>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-3 p-4 bg-slate-900/40 border border-slate-850 rounded-2xl">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-455 uppercase tracking-wider mb-1">Full Name</label>
                        <input name="name" type="text" required value="{{ Auth::user()->name }}" class="w-full px-3 py-2 rounded-xl bg-slate-955 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-455 uppercase tracking-wider mb-1">Email Address</label>
                        <input name="email" type="email" required value="{{ Auth::user()->email }}" class="w-full px-3 py-2 rounded-xl bg-slate-955 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-455 uppercase tracking-wider mb-1">New Password (Leave blank to keep current)</label>
                        <input name="password" type="password" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl bg-slate-955 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-455 uppercase tracking-wider mb-1">Confirm New Password</label>
                        <input name="password_confirmation" type="password" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl bg-slate-955 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                    </div>
                    <button type="submit" class="w-full py-2 px-3 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-brand-600/10">
                        Update Settings
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
