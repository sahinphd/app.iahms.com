@extends('layouts.app')

@section('page_title', 'Course Syllabus')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">

    <!-- Course Banner Hero -->
    <div class="relative bg-slate-950/60 border border-slate-800 rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row gap-6 items-center">
            
            <div class="h-32 w-32 md:h-40 md:w-48 bg-slate-900 rounded-2xl border border-slate-800/80 overflow-hidden flex-shrink-0 flex items-center justify-center">
                @if($course->thumbnail)
                    <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="h-full w-full object-cover">
                @else
                    <svg class="h-10 w-10 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                @endif
            </div>

            <div class="flex-1 text-center md:text-left space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-500/20 text-brand-400 border border-brand-500/20">
                        Instructor: {{ $course->teacher->name }}
                    </span>
                    @if($course->duration)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                            Duration: {{ $course->duration }}
                        </span>
                    @endif
                    @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToCourse($course, 'course_admin'))
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $course->is_published ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500 border border-slate-800' }}">
                            {{ $course->is_published ? 'Published' : 'Draft' }}
                        </span>
                    @endif
                    @if($course->is_completed)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/20">
                            Course Completed
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight leading-tight">{{ $course->title }}</h2>
                <p class="text-xs md:text-sm text-slate-400 max-w-2xl leading-relaxed">{{ $course->description }}</p>
            </div>

            <!-- Enrollment State Control -->
            <div class="flex-shrink-0 w-full md:w-auto text-center md:text-right">
                @if(Auth::user()->isStudent())
                    @if($isEnrolled)
                        <div class="inline-flex flex-col items-center md:items-end">
                            <span class="px-3.5 py-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold select-none flex items-center space-x-1.5 mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>You are Enrolled</span>
                            </span>
                            <form action="{{ route('enrollments.unenroll', $course->id) }}" method="POST" onsubmit="return confirm('Unenroll from this course?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-medium transition-colors">
                                    Unenroll Course
                                </button>
                            </form>
                        </div>
                    @elseif($isPending)
                        <div class="inline-flex flex-col items-center md:items-end">
                            <span class="px-3.5 py-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-bold select-none flex items-center space-x-1.5 mb-2">
                                <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Pending Approval</span>
                            </span>
                            <form action="{{ route('enrollments.unenroll', $course->id) }}" method="POST" onsubmit="return confirm('Cancel enrollment request?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-medium transition-colors">
                                    Cancel Request
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('enrollments.enroll') }}" method="POST" class="inline-block">
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-bold transition-all duration-200 shadow-lg shadow-brand-500/20 transform hover:-translate-y-0.5">
                                Enroll Now
                            </button>
                        </form>
                    @endif
                @endif

                @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToCourse($course, 'course_admin'))
                    <a href="{{ route('courses.edit', $course->id) }}" class="inline-block px-5 py-2.5 rounded-xl border border-slate-800 hover:bg-slate-900 text-xs font-bold text-slate-300 transition-colors">
                        Edit Settings
                    </a>
                @endif
            </div>

        </div>
    </div>

    <!-- Course Layout Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Syllabus Content (Subjects -> Modules) -->
        <div class="lg:col-span-2 space-y-6">

            @if(count($pendingEnrollments) > 0)
                <div class="bg-slate-950/60 border border-amber-500/20 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center space-x-2.5">
                        <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/10">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100">Pending Enrollment Requests</h3>
                            <p class="text-[10px] text-slate-400">Student enrollment approval required for class/course access</p>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-slate-850">
                        @foreach($pendingEnrollments as $pending)
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-200">{{ $pending->student->name }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $pending->student->email }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="{{ route('enrollments.approve', $pending->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-[10px] font-bold transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('enrollments.unenroll', $course->id) }}" method="POST" onsubmit="return confirm('Reject this enrollment request?')" class="inline">
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
            
            <div class="space-y-6">
                @if(Auth::user()->isStudent() && !$isEnrolled)
                    <!-- Unenrolled / Pending Block -->
                    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                        <div class="text-center py-16 px-4 bg-slate-900/20 border border-slate-850 rounded-2xl">
                            <svg class="w-12 h-12 text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h4 class="text-base font-bold text-slate-300">Syllabus Locked</h4>
                            @if($isPending)
                                <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto">Your enrollment request is pending approval. Once approved by the course teacher or administrator, you will be able to watch videos and download study materials.</p>
                            @else
                                <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto">Please enroll in this course above to access the lectures, download PDFs, and attend classes.</p>
                            @endif
                        </div>
                    </div>
                @else
                    @if(Auth::user()->isStudent())
                        <!-- Course Progress Bar Banner -->
                        <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-5 shadow-lg mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-200">Your Course Progress</h4>
                                <p class="text-[10px] text-slate-400">Complete video lectures to advance</p>
                            </div>
                            <div class="flex items-center space-x-4 flex-1 sm:max-w-xs">
                                <div class="w-full bg-slate-900 rounded-full h-2.5 border border-slate-800">
                                    <div class="bg-brand-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $courseProgressPercent }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-brand-400 min-w-[32px] text-right">{{ $courseProgressPercent }}%</span>
                            </div>
                        </div>
                    @endif

                    @if(count($course->subjects) > 0)
                        <!-- Horizontal Scrollable Tabs for Subjects -->
                        <div class="mb-6 border-b border-slate-800/80 pb-3 flex items-center justify-between gap-4">
                            <div class="flex items-center space-x-2 overflow-x-auto py-1 scrollbar-thin scrollbar-thumb-slate-800 flex-1">
                                @foreach($course->subjects as $index => $sub)
                                    <button onclick="switchSubject({{ $sub->id }})" 
                                            id="subject-tab-{{ $sub->id }}" 
                                            class="subject-tab-btn flex-shrink-0 px-4 py-2.5 rounded-xl border transition-all duration-200 text-xs font-bold flex items-center space-x-2 {{ $index === 0 ? 'bg-brand-500/15 text-brand-400 border-brand-500/30 shadow-md shadow-brand-500/5' : 'bg-slate-900/60 text-slate-400 border-slate-800 hover:text-slate-350 hover:bg-slate-850' }}">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span>{{ $sub->title }}</span>
                                        @if($sub->duration)
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-normal {{ $index === 0 ? 'bg-brand-500/20 text-brand-350' : 'bg-slate-950 text-slate-500' }}">{{ $sub->duration }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Loop through Subjects -->
                    @forelse($course->subjects as $index => $subject)
                        <div id="subject-content-{{ $subject->id }}" class="subject-content-pane bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-6 {{ $index === 0 ? '' : 'hidden' }}">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-850 pb-4">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-500/10 text-brand-400 border border-brand-500/10">Subject</span>
                                        @if($subject->duration)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-slate-400 border border-slate-850">Duration: {{ $subject->duration }}</span>
                                        @endif
                                    </div>
                                    <h4 class="text-lg font-extrabold text-slate-100">{{ $subject->title }}</h4>
                                    @if($subject->description)
                                        <p class="text-xs text-slate-400 leading-relaxed">{{ $subject->description }}</p>
                                    @endif
                                </div>
                                
                                <!-- Subject level actions -->
                                <div class="flex flex-wrap items-center gap-2">
                                    @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToCourse($course, 'course_admin'))
                                        <button type="button" onclick="toggleModal('assign-subject-teachers-modal-{{ $subject->id }}')" class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-850 border border-slate-800 text-[10px] font-bold text-slate-300 rounded-xl transition-colors">
                                            Assign Teachers
                                        </button>
                                        
                                        <button type="button" onclick="toggleModal('add-module-modal-{{ $subject->id }}')" class="px-2.5 py-1.5 bg-brand-600 hover:bg-brand-500 text-[10px] font-bold text-white rounded-xl transition-colors">
                                            + Add Module
                                        </button>

                                        <button type="button" onclick="toggleModal('add-live-class-modal-{{ $subject->id }}')" class="px-2.5 py-1.5 bg-indigo-650 hover:bg-indigo-600 text-[10px] font-bold text-white rounded-xl transition-colors">
                                            + Live Class
                                        </button>

                                        <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" onsubmit="return confirm('Delete this subject and all its content?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 bg-rose-950/40 hover:bg-rose-900/40 border border-rose-900/30 text-[10px] font-bold text-rose-405 rounded-xl transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Subject Teachers Roster -->
                            <div class="flex flex-wrap gap-2 items-center text-xs pb-2">
                                <span class="text-slate-500 font-semibold text-[10px] uppercase tracking-wider">Subject Teachers:</span>
                                @forelse($subject->teachers as $subTeacher)
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-slate-900 border border-slate-855 text-[10px] text-slate-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>{{ $subTeacher->name }} ({{ $subTeacher->pivot->role === 'subject_teacher' ? 'Subject Teacher' : 'Assistant' }})</span>
                                    </span>
                                @empty
                                    <span class="text-[10px] text-slate-500 italic">No teachers specifically assigned. Inherits Course Administrators.</span>
                                @endforelse
                            </div>

                            <!-- Modules Accordion -->
                            <div class="space-y-4 pt-2">
                                @forelse($subject->modules as $module)
                                    <div class="bg-slate-900/25 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-700/60 transition-all duration-200">
                                        <!-- Header (Click to expand) -->
                                        <div onclick="toggleModuleAccordion({{ $module->id }})" 
                                             class="w-full flex items-center justify-between p-4 cursor-pointer hover:bg-slate-900/60 transition-colors select-none">
                                            
                                            <div class="flex items-center space-x-3 min-w-0">
                                                <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-lg bg-brand-500/10 text-brand-400 text-xs font-extrabold uppercase">M</span>
                                                <div class="min-w-0">
                                                    <h5 class="text-sm font-bold text-slate-200 truncate">{{ $module->title }}</h5>
                                                    <div class="flex items-center space-x-2 mt-0.5 text-[10px] text-slate-500 font-medium">
                                                        <span>{{ count($module->lectures) }} Video{{ count($module->lectures) !== 1 ? 's' : '' }}</span>
                                                        <span>•</span>
                                                        <span>{{ count($module->materials) }} Material{{ count($module->materials) !== 1 ? 's' : '' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center space-x-3 flex-shrink-0">
                                                <!-- Action controls -->
                                                @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToSubject($subject))
                                                    <div class="flex items-center space-x-2 mr-2" onclick="event.stopPropagation();">
                                                        <button onclick="toggleModal('add-lecture-modal-{{ $module->id }}')" class="px-2 py-1 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 text-[10px] font-bold text-indigo-400 hover:text-indigo-300 rounded-lg transition-all">+ Video</button>
                                                        <button onclick="toggleModal('add-material-modal-{{ $module->id }}')" class="px-2 py-1 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-[10px] font-bold text-amber-400 hover:text-amber-300 rounded-lg transition-all">+ PDF</button>
                                                        
                                                        <form action="{{ route('modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Delete this module and all its contents?')" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1 text-slate-600 hover:text-rose-400 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif

                                                <!-- Chevron Indicator -->
                                                <svg id="module-chevron-{{ $module->id }}" 
                                                     class="w-4 h-4 text-slate-500 transition-transform duration-200 {{ $loop->first ? 'transform rotate-180' : '' }}" 
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Collapsable Body Content -->
                                        <div id="module-body-{{ $module->id }}" class="module-body-pane {{ $loop->first ? '' : 'hidden' }} border-t border-slate-800 bg-slate-900/10 p-5 space-y-4">
                                            <!-- Lectures & Materials list -->
                                            <div class="space-y-2.5 pl-4 border-l border-slate-800">
                                                <!-- Video Lectures -->
                                                @foreach($module->lectures as $lecture)
                                                    <div class="flex items-center justify-between p-3 bg-slate-950/40 border border-slate-850 rounded-xl hover:border-slate-800 transition-colors">
                                                        <div class="flex items-center space-x-2.5 min-w-0">
                                                            @if(Auth::user()->isStudent())
                                                                @if(in_array($lecture->id, $completedLectureIds))
                                                                    <!-- Green Check -->
                                                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                @elseif(isset($lectureProgressMap[$lecture->id]))
                                                                    <!-- Orange dot -->
                                                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse flex-shrink-0 mx-1"></span>
                                                                @else
                                                                    <!-- Empty circle -->
                                                                    <span class="w-2 h-2 rounded-full bg-slate-700 flex-shrink-0 mx-1"></span>
                                                                @endif
                                                            @else
                                                                <svg class="w-4 h-4 text-brand-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @endif
                                                            <span class="text-xs text-slate-300 truncate">{{ $lecture->title }}</span></span>
                                                            @if($lecture->duration)
                                                                <span class="text-[9px] text-slate-500 bg-slate-900 px-1.5 py-0.5 rounded border border-slate-800 font-medium">{{ $lecture->duration }}</span>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="flex items-center space-x-3 flex-shrink-0">
                                                            <a href="{{ route('lectures.show', $lecture->id) }}" class="text-[10px] font-bold text-brand-400 hover:text-brand-300 transition-colors">Watch Video</a>
                                                            
                                                            @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToSubject($subject))
                                                                <form action="{{ route('lectures.destroy', $lecture->id) }}" method="POST" onsubmit="return confirm('Delete this video lecture?')" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-slate-600 hover:text-rose-400 transition-colors">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach

                                                <!-- Study Materials -->
                                                @foreach($module->materials as $material)
                                                    <div class="flex items-center justify-between p-3 bg-slate-950/40 border border-slate-850 rounded-xl hover:border-slate-800 transition-colors">
                                                        <div class="flex items-center space-x-2.5 min-w-0">
                                                            <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <span class="text-xs text-slate-300 truncate">{{ $material->title }}</span>
                                                        </div>

                                                        <div class="flex items-center space-x-3 flex-shrink-0">
                                                            <a href="{{ route('materials.download', $material->id) }}" target="_blank" class="text-[10px] font-bold text-amber-400 hover:text-amber-300 transition-colors">Download PDF</a>

                                                            @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToSubject($subject))
                                                                <form action="{{ route('materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Delete this study material?')" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-slate-600 hover:text-rose-400 transition-colors">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if(count($module->lectures) == 0 && count($module->materials) == 0)
                                                    <p class="text-[10px] text-slate-600 italic py-1">No content uploaded to this module yet.</p>
                                                @endif
                                            </div>

                                            <!-- Modals for Lecture and Material creation inside module -->
                                            @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToSubject($subject))
                                                
                                                <!-- Add Lecture Modal -->
                                                <div id="add-lecture-modal-{{ $module->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                                                    <div class="bg-slate-955 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
                                                        <h4 class="text-sm font-bold text-slate-100">Add Lecture to "{{ $module->title }}"</h4>
                                                        <form action="{{ route('lectures.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                            @csrf
                                                            <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                            <div>
                                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Lecture Title</label>
                                                                <input name="title" type="text" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Duration (e.g. 15 Mins, 1 Hour)</label>
                                                                <input name="duration" type="text" placeholder="e.g. 15 Mins" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Video File</label>
                                                                <input name="video" type="file" accept="video/*" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-450 focus:outline-none">
                                                                <span class="text-[9px] text-slate-600 mt-1 block">MP4 or standard video up to 100MB</span>
                                                            </div>
                                                            <div class="flex justify-end space-x-2 pt-2">
                                                                <button type="button" onclick="toggleModal('add-lecture-modal-{{ $module->id }}')" class="px-3 py-1.5 rounded-xl border border-slate-800 text-xs font-semibold text-slate-400 hover:bg-slate-900">Cancel</button>
                                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold">Upload Lecture</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <!-- Add Material Modal -->
                                                <div id="add-material-modal-{{ $module->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                                                    <div class="bg-slate-955 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
                                                        <h4 class="text-sm font-bold text-slate-100">Add Study Material to "{{ $module->title }}"</h4>
                                                        <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                            @csrf
                                                            <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                            <div>
                                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Material Title</label>
                                                                <input name="title" type="text" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Document File</label>
                                                                <input name="material_file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-450 focus:outline-none">
                                                                <span class="text-[9px] text-slate-600 mt-1 block">PDF, DOC, PPT, ZIP up to 50MB</span>
                                                            </div>
                                                            <div class="flex justify-end space-x-2 pt-2">
                                                                <button type="button" onclick="toggleModal('add-material-modal-{{ $module->id }}')" class="px-3 py-1.5 rounded-xl border border-slate-800 text-xs font-semibold text-slate-400 hover:bg-slate-900">Cancel</button>
                                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold">Upload Document</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-550 italic">No modules created yet in this subject.</p>
                                @endforelse
                            </div>

                            <!-- Subject Live Classes Section -->
                            @if(count($upcomingLiveClasses[$subject->id]) > 0 || count($pastLiveClasses[$subject->id]) > 0)
                                <div class="pt-4 border-t border-slate-850 space-y-4">
                                    @if(count($upcomingLiveClasses[$subject->id]) > 0)
                                        <div>
                                            <h5 class="text-xs font-bold text-indigo-400 mb-3 flex items-center space-x-1.5">
                                                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                                <span>Upcoming Live Classes</span>
                                            </h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($upcomingLiveClasses[$subject->id] as $liveClass)
                                                    <div class="p-3.5 bg-slate-900/40 border border-slate-850 rounded-2xl flex justify-between items-center hover:border-slate-800 transition-all">
                                                        <div>
                                                            <h6 class="text-xs font-bold text-slate-200">{{ $liveClass->title }}</h6>
                                                            <div class="flex items-center space-x-2 mt-1">
                                                                <span class="text-[10px] text-slate-400 bg-slate-950 px-1.5 py-0.5 rounded border border-slate-850">{{ $liveClass->datetime->format('M d, H:i') }}</span>
                                                                <span class="text-[10px] text-slate-500 font-medium">({{ $liveClass->duration_minutes }} min session)</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            <a href="{{ route('live-classes.join', $liveClass->id) }}" target="_blank" class="px-3 py-1.5 bg-indigo-650 hover:bg-indigo-600 text-white text-[10px] font-bold rounded-xl transition-all font-sans">
                                                                Join Meet
                                                            </a>
                                                            @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToSubject($subject))
                                                                <form action="{{ route('live-classes.destroy', $liveClass->id) }}" method="POST" onsubmit="return confirm('Cancel this live class session?')" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="p-1.5 text-slate-600 hover:text-rose-400 transition-colors">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if(count($pastLiveClasses[$subject->id]) > 0)
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-400 mb-3 flex items-center space-x-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-500 animate-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Live Classes History</span>
                                            </h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($pastLiveClasses[$subject->id] as $liveClass)
                                                    <div class="p-3.5 bg-slate-900/20 border border-slate-850 rounded-2xl flex justify-between items-center opacity-85">
                                                        <div>
                                                            <h6 class="text-xs font-semibold text-slate-300">{{ $liveClass->title }}</h6>
                                                            <div class="flex items-center space-x-2 mt-1">
                                                                <span class="text-[9px] text-slate-550 bg-slate-950 px-1.5 py-0.5 rounded border border-slate-850">{{ $liveClass->datetime->format('M d, H:i') }}</span>
                                                                <span class="text-[9px] text-slate-600 font-medium">({{ $liveClass->duration_minutes }} min session)</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            @if(Auth::user()->isStudent())
                                                                @if(in_array($liveClass->id, $attendedLiveClassIds))
                                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-450 border border-emerald-500/20">
                                                                        Attended
                                                                    </span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                                        Missed
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="text-[9px] text-slate-500 font-bold uppercase">Archived</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Subject Modals: Assign Teachers, Add Module, Schedule Live Class -->
                            @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToCourse($course, 'course_admin'))
                                <!-- Assign Subject Teachers Modal -->
                                <div id="assign-subject-teachers-modal-{{ $subject->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                                    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
                                        <h4 class="text-sm font-bold text-slate-100">Assign Teachers to "{{ $subject->title }}"</h4>
                                        <form action="{{ route('subjects.assign-teachers', $subject->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                                                @foreach($allTeachers as $teacher)
                                                    @php
                                                        $assigned = $subject->teachers->firstWhere('id', $teacher->id);
                                                        $currentRole = $assigned ? $assigned->pivot->role : 'subject_teacher';
                                                    @endphp
                                                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-850">
                                                        <label class="flex items-center space-x-2.5 text-xs text-slate-200 cursor-pointer">
                                                            <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}" {{ $assigned ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-950 text-brand-600 focus:ring-brand-500">
                                                            <span>{{ $teacher->name }}</span>
                                                        </label>
                                                        <select name="roles[{{ $teacher->id }}]" class="bg-slate-950 border border-slate-800 rounded-lg text-[10px] text-slate-350 py-1 px-2 focus:outline-none focus:border-brand-500">
                                                            <option value="subject_teacher" {{ $currentRole === 'subject_teacher' ? 'selected' : '' }}>Subject Teacher</option>
                                                            <option value="assistant" {{ $currentRole === 'assistant' ? 'selected' : '' }}>Assistant</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="flex justify-end space-x-2 pt-2">
                                                <button type="button" onclick="toggleModal('assign-subject-teachers-modal-{{ $subject->id }}')" class="px-3 py-1.5 rounded-xl border border-slate-800 text-xs font-semibold text-slate-400 hover:bg-slate-900">Cancel</button>
                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold">Save Assignments</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Add Module Modal -->
                                <div id="add-module-modal-{{ $subject->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                                    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
                                        <h4 class="text-sm font-bold text-slate-100">Add Curriculum Module to "{{ $subject->title }}"</h4>
                                        <form action="{{ route('modules.store') }}" method="POST" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Module Title</label>
                                                <input name="title" type="text" required placeholder="e.g. Chapter 1: Foundations" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                                            </div>
                                            <div class="flex justify-end space-x-2 pt-2">
                                                <button type="button" onclick="toggleModal('add-module-modal-{{ $subject->id }}')" class="px-3 py-1.5 rounded-xl border border-slate-800 text-xs font-semibold text-slate-400 hover:bg-slate-900">Cancel</button>
                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold">Create Module</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Schedule Live Class Modal -->
                                <div id="add-live-class-modal-{{ $subject->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                                    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
                                        <h4 class="text-sm font-bold text-slate-100">Schedule Live Class for "{{ $subject->title }}"</h4>
                                        <form action="{{ route('live-classes.store') }}" method="POST" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                            
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Class Title</label>
                                                <input name="title" type="text" required placeholder="e.g. Clinical Q&A Workshop" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Date & Time</label>
                                                    <input name="datetime" type="datetime-local" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 focus:outline-none focus:border-brand-500">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Duration (minutes)</label>
                                                    <input name="duration_minutes" type="number" required min="5" max="480" value="60" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Google Meet Link</label>
                                                <input name="link" type="url" required placeholder="https://meet.google.com/..." class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500">
                                            </div>

                                            <div class="flex justify-end space-x-2 pt-2">
                                                <button type="button" onclick="toggleModal('add-live-class-modal-{{ $subject->id }}')" class="px-3 py-1.5 rounded-xl border border-slate-800 text-xs font-semibold text-slate-400 hover:bg-slate-900">Cancel</button>
                                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold">Schedule Class</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="text-center py-12 text-slate-500 border border-dashed border-slate-800 rounded-3xl">No subjects created for this course yet.</div>
                    @endforelse
                @endif
            </div>

            <!-- Course Subject Creator form -->
            @if(Auth::user()->isAdmin() || Auth::user()->isAssignedToCourse($course, 'course_admin'))
                <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                    <h3 class="text-base font-bold text-slate-200">Add New Subject Section</h3>
                    <p class="text-xs text-slate-400">Create a distinct curricular module/subject node (e.g. Physiology, Radiographic Positioning) inside this program.</p>
                    <form action="{{ route('subjects.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Subject Title</label>
                                <input name="title" type="text" required placeholder="e.g. Chest Radiography" class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-800 text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Duration (e.g. 4 Weeks, 10 Hours)</label>
                                <input name="duration" type="text" placeholder="e.g. 4 Weeks" class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-800 text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</label>
                            <textarea name="description" rows="2" placeholder="Brief outline of this syllabus section..." class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-800 text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700"></textarea>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-colors">
                            Create Subject Node
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Right Side Panel: Course Administration -->
        <div class="space-y-6">

            <!-- Course Teacher Assignments Card -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                <h3 class="text-base font-bold text-slate-200">Course Administration</h3>
                <p class="text-xs text-slate-400">Assigned Course Administrators and Teachers</p>
                
                <div class="space-y-3">
                    @forelse($course->teachers as $teacher)
                        <div class="flex items-center justify-between p-3 bg-slate-900/40 border border-slate-850 rounded-2xl">
                            <div>
                                <p class="text-xs font-bold text-slate-200">{{ $teacher->name }}</p>
                                <p class="text-[9px] text-slate-500">{{ $teacher->email }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase {{ $teacher->pivot->role === 'course_admin' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-850 text-slate-450 border border-slate-800' }}">
                                {{ $teacher->pivot->role === 'course_admin' ? 'Course Admin' : 'Teacher' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic">No teachers assigned to this course yet.</p>
                    @endforelse
                </div>

                @if(Auth::user()->isAdmin())
                    <!-- Admin Teacher Assignment Form -->
                    <div class="pt-4 border-t border-slate-850/60 space-y-3">
                        <h4 class="text-xs font-bold text-slate-350">Sync Course Teachers</h4>
                        <form action="{{ route('admin.courses.assign-teachers', $course->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-1">
                                @foreach($allTeachers as $teacher)
                                    @php
                                        $assigned = $course->teachers->firstWhere('id', $teacher->id);
                                        $currentRole = $assigned ? $assigned->pivot->role : 'teacher';
                                    @endphp
                                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-850">
                                        <label class="flex items-center space-x-2.5 text-xs text-slate-200 cursor-pointer">
                                            <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}" {{ $assigned ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-950 text-brand-600 focus:ring-brand-500">
                                            <span>{{ $teacher->name }}</span>
                                        </label>
                                        <select name="roles[{{ $teacher->id }}]" class="bg-slate-950 border border-slate-800 rounded-lg text-[9px] text-slate-300 py-1 px-1.5 focus:outline-none focus:border-brand-500">
                                            <option value="course_admin" {{ $currentRole === 'course_admin' ? 'selected' : '' }}>Course Admin</option>
                                            <option value="teacher" {{ $currentRole === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit" class="w-full py-2 px-3 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all duration-200 shadow-md shadow-brand-600/10">
                                Sync Teachers
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Additional Course Info / Batch Stats Widget -->
            @if($course->schoolClass)
                <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                    <h3 class="text-base font-bold text-slate-200">Associated Class</h3>
                    <div class="p-3 bg-slate-900/40 border border-slate-850 rounded-2xl space-y-2">
                        <p class="text-xs font-bold text-slate-200">{{ $course->schoolClass->name }}</p>
                        <div class="flex justify-between items-center text-[10px] text-slate-400">
                            <span>Students enrolled:</span>
                            <span class="font-bold text-slate-200">{{ $course->schoolClass->students()->count() }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>

<!-- Vanilla JS Controller -->
<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }
    }

    function switchSubject(subjectId) {
        // Hide all subject content panels
        document.querySelectorAll('.subject-content-pane').forEach(el => {
            el.classList.add('hidden');
        });
        // Show selected subject panel
        const activePane = document.getElementById('subject-content-' + subjectId);
        if (activePane) {
            activePane.classList.remove('hidden');
        }

        // Reset all tab buttons classes
        document.querySelectorAll('.subject-tab-btn').forEach(btn => {
            btn.className = "subject-tab-btn flex-shrink-0 px-4 py-2.5 rounded-xl border transition-all duration-200 text-xs font-bold flex items-center space-x-2 bg-slate-900/60 text-slate-400 border-slate-800 hover:text-slate-350 hover:bg-slate-850";
            
            // Adjust inner badge colors if exists
            const badge = btn.querySelector('span.text-\\[9px\\]');
            if (badge) {
                badge.className = "text-[9px] px-1.5 py-0.5 rounded font-normal bg-slate-950 text-slate-500";
            }
        });

        // Set active tab button styles
        const activeTab = document.getElementById('subject-tab-' + subjectId);
        if (activeTab) {
            activeTab.className = "subject-tab-btn flex-shrink-0 px-4 py-2.5 rounded-xl border transition-all duration-200 text-xs font-bold flex items-center space-x-2 bg-brand-500/15 text-brand-400 border-brand-500/30 shadow-md shadow-brand-500/5";
            
            // Highlight inner badge color
            const badge = activeTab.querySelector('span.text-\\[9px\\]');
            if (badge) {
                badge.className = "text-[9px] px-1.5 py-0.5 rounded font-normal bg-brand-500/20 text-brand-350";
            }
        }
    }

    function toggleModuleAccordion(moduleId) {
        const body = document.getElementById('module-body-' + moduleId);
        const chevron = document.getElementById('module-chevron-' + moduleId);
        if (body && chevron) {
            if (body.classList.contains('hidden')) {
                body.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                body.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }
    }
</script>
@endsection
