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
        
        <!-- Courses List column -->
        <div class="xl:col-span-2 space-y-6">
            <!-- Pending Enrollments List -->
            @if(count($pendingEnrollments) > 0)
                <div class="bg-slate-950/40 border border-amber-500/25 rounded-3xl p-6 shadow-lg space-y-4">
                    <div class="flex items-center space-x-2.5">
                        <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/10">
                            <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100">Pending Enrollment Requests</h3>
                            <p class="text-[10px] text-slate-400">Students waiting for access to your courses</p>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-slate-850">
                        @foreach($pendingEnrollments as $pending)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-3.5 first:pt-0 last:pb-0 gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-200">{{ $pending->student->name }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $pending->student->email }}</p>
                                    <p class="text-[9px] text-brand-400 mt-0.5">Course: {{ $pending->course->title }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="{{ route('enrollments.approve', $pending->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-[10px] font-bold transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('enrollments.unenroll', $pending->course_id) }}" method="POST" onsubmit="return confirm('Reject this enrollment request?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="student_id" value="{{ $pending->student_id }}">
                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-[10px] font-bold transition-colors">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- My Courses List -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
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
                            <div class="min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-sm font-bold text-slate-200 truncate">{{ $course->title }}</h4>
                                    @if($course->is_completed)
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded">Completed</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5 max-w-sm truncate">{{ $course->description }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2">
                            @if(Auth::user()->isAssignedToCourse($course, 'course_admin'))
                                <form action="{{ route('courses.toggle-completion', $course->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl border {{ $course->is_completed ? 'border-emerald-500/30 bg-emerald-950/20 hover:bg-emerald-900/20 text-emerald-400' : 'border-slate-800 hover:bg-slate-850 text-slate-350 hover:text-slate-205' }} text-xs font-semibold transition-all duration-200">
                                        {{ $course->is_completed ? 'Mark Active' : 'Mark Completed' }}
                                    </button>
                                </form>
                            @endif

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
                    <div class="text-center py-12 text-slate-500 border border-dashed border-slate-855 rounded-2xl bg-slate-900/10">
                        <p class="text-sm font-medium">No courses created yet.</p>
                        <p class="text-xs text-slate-650 mt-1">Get started by creating your first curriculum above.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- My Assigned Classes -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-100">My Assigned Classes</h3>
                    <p class="text-xs text-slate-400 mt-1">View rosters and manage class-level details</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($myClasses as $class)
                    <div class="p-5 bg-slate-900/50 border border-slate-800/80 rounded-2xl flex flex-col justify-between hover:border-slate-750 transition-colors gap-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold text-slate-200 flex items-center space-x-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                                    <span>{{ $class->name }}</span>
                                </h4>
                                <span class="px-2 py-0.5 rounded text-[8px] font-extrabold uppercase {{ $class->pivot->role === 'class_admin' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $class->pivot->role === 'class_admin' ? 'Class Admin' : 'Teacher' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $class->description ?: 'No description provided.' }}</p>
                        </div>

                        <div class="flex items-center justify-between mt-2 pt-3 border-t border-slate-850/80">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $class->students()->count() }} Enrolled Students</span>
                            <a href="{{ route('classes.show', $class->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-750 text-[10px] font-bold text-slate-200 transition-colors">
                                View Class Details
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-10 text-slate-500 border border-dashed border-slate-855 rounded-2xl bg-slate-900/10">
                        <p class="text-sm font-medium">No school classes assigned to you.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side Panel: noticeboard & live classes -->
        <div class="space-y-6">

            <!-- Class Announcements Noticeboard -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                <div>
                    <h3 class="text-base font-bold text-slate-100 mb-1">Class Noticeboard</h3>
                    <p class="text-xs text-slate-400">Post announcements and bulletins to your assigned classes</p>
                </div>

                @if(count($myClasses) > 0)
                    <form action="{{ route('teacher.class-notes.store') }}" method="POST" class="space-y-3 p-4 bg-slate-900/40 border border-slate-850 rounded-2xl">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-450 uppercase tracking-wider mb-1">Target Class</label>
                            <select name="school_class_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                                <option value="all" class="bg-slate-950 text-slate-200">All Classes (Global Notice)</option>
                                @foreach($myClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-450 uppercase tracking-wider mb-1">Notice Title</label>
                            <input name="title" type="text" required placeholder="e.g. Schedule Change for Radiography" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-450 uppercase tracking-wider mb-1">Content</label>
                            <textarea name="content" rows="3" required placeholder="Write your notice here..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2 px-3 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-brand-600/10">
                            Post Announcement
                        </button>
                    </form>
                @else
                    <p class="text-xs text-slate-500 italic p-3 bg-slate-900/30 border border-slate-850 rounded-2xl">
                        You must be assigned to a School Class as Class Admin to post notices.
                    </p>
                @endif

                <!-- List of Past Notices -->
                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                    <h4 class="text-xs font-bold text-slate-350">Recent Announcements</h4>
                    @forelse($classNotes as $note)
                        <div class="p-3.5 bg-slate-900/50 border border-slate-850 rounded-2xl space-y-2 relative">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-brand-500/10 text-brand-400 border border-brand-500/20">
                                        {{ $note->schoolClass ? $note->schoolClass->name : 'All Classes' }}
                                    </span>
                                    <h5 class="text-xs font-bold text-slate-200 mt-1">{{ $note->title }}</h5>
                                </div>
                                <form action="{{ route('teacher.class-notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Remove this announcement?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-600 hover:text-rose-450 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">{{ $note->content }}</p>
                            <p class="text-[9px] text-slate-550 text-right">{{ $note->created_at->format('M d, H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-600 text-center py-4">No announcements posted yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Live Classes -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col">
                <h3 class="text-base font-bold text-slate-200 mb-1">Upcoming Live Classes</h3>
                <p class="text-xs text-slate-450 mb-4">Schedule links for your enrolled students</p>

                <div class="space-y-4 overflow-y-auto max-h-[300px]">
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
                                <button type="submit" class="text-slate-500 hover:text-rose-450 transition-colors">
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
                    <div class="text-center py-8 text-xs text-slate-600">No upcoming classes scheduled.</div>
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
