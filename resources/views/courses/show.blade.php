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
                    <svg class="h-10 w-10 text-slate-750" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                @endif
            </div>

            <div class="flex-1 text-center md:text-left space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-500/20 text-brand-400 border border-brand-500/20">
                        Instructor: {{ $course->teacher->name }}
                    </span>
                    @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $course->is_published ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500 border border-slate-800' }}">
                            {{ $course->is_published ? 'Published' : 'Draft' }}
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

                @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                    <a href="{{ route('courses.edit', $course->id) }}" class="inline-block px-5 py-2.5 rounded-xl border border-slate-800 hover:bg-slate-900 text-xs font-bold text-slate-300 transition-colors">
                        Edit Settings
                    </a>
                @endif
            </div>

        </div>
    </div>

    <!-- Course Layout Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Syllabus Content (Modules & Chapters) -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-100">Course Syllabus</h3>
                        <p class="text-xs text-slate-400 mt-1">Modules, lecture video materials, and reference files</p>
                    </div>
                </div>

                @if(Auth::user()->isStudent() && !$isEnrolled)
                    <!-- Unenrolled Block -->
                    <div class="text-center py-16 px-4 bg-slate-900/20 border border-slate-850 rounded-2xl">
                        <svg class="w-12 h-12 text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h4 class="text-base font-bold text-slate-300">Syllabus Locked</h4>
                        <p class="text-xs text-slate-500 mt-2 max-w-sm mx-auto">Please enroll in this course above to access the lectures, download PDFs, and attend classes.</p>
                    </div>
                @else
                    <!-- Syllabus Accordion/List -->
                    <div class="space-y-6">
                        @forelse($course->modules as $module)
                            <div class="p-5 bg-slate-900/40 border border-slate-800 rounded-2xl space-y-4">
                                <div class="flex justify-between items-start">
                                    <h4 class="text-sm font-bold text-slate-200 flex items-center space-x-2">
                                        <span class="flex-shrink-0 flex items-center justify-center w-5 h-5 rounded bg-brand-500/10 text-brand-400 text-[10px] font-extrabold uppercase">M</span>
                                        <span>{{ $module->title }}</span>
                                    </h4>

                                    @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                                        <!-- Actions for Module -->
                                        <div class="flex items-center space-x-2">
                                            <button onclick="toggleModal('add-lecture-modal-{{ $module->id }}')" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300 transition-colors">+ Video</button>
                                            <span class="text-slate-800 text-[10px]">•</span>
                                            <button onclick="toggleModal('add-material-modal-{{ $module->id }}')" class="text-[10px] font-bold text-amber-400 hover:text-amber-300 transition-colors">+ PDF</button>
                                            <span class="text-slate-800 text-[10px]">•</span>
                                            <form action="{{ route('modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Delete this module and all its lectures/materials?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[10px] font-bold text-rose-500 hover:text-rose-400 transition-colors">Delete</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>

                                <!-- Lectures inside module -->
                                <div class="space-y-2.5 pl-7 border-l border-slate-850">
                                    @foreach($module->lectures as $lecture)
                                        <div class="flex items-center justify-between p-3 bg-slate-950/40 border border-slate-850 rounded-xl hover:border-slate-800 transition-colors">
                                            <div class="flex items-center space-x-2.5 min-w-0">
                                                <svg class="w-4 h-4 text-brand-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-xs text-slate-300 truncate">{{ $lecture->title }}</span>
                                            </div>
                                            
                                            <div class="flex items-center space-x-3 flex-shrink-0">
                                                <a href="{{ route('lectures.show', $lecture->id) }}" class="text-[10px] font-bold text-brand-400 hover:text-brand-300 transition-colors">Watch Video</a>
                                                
                                                @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                                                    <form action="{{ route('lectures.destroy', $lecture->id) }}" method="POST" onsubmit="return confirm('Delete this video lecture?')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-slate-650 hover:text-rose-400 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Materials inside module -->
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

                                                @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                                                    <form action="{{ route('materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Delete this study material?')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-slate-655 hover:text-rose-400 transition-colors">
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
                                        <p class="text-[10px] text-slate-600 py-1">No content uploaded to this module yet.</p>
                                    @endif
                                </div>

                                <!-- Modals for Lecture and Material creation inline inside modules loop -->
                                @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                                    
                                    <!-- Add Lecture Modal -->
                                    <div id="add-lecture-modal-{{ $module->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                                        <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
                                            <h4 class="text-sm font-bold text-slate-100 mb-4">Add Lecture to "{{ $module->title }}"</h4>
                                            <form action="{{ route('lectures.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                @csrf
                                                <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Lecture Title</label>
                                                    <input name="title" type="text" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Video File</label>
                                                    <input name="video" type="file" accept="video/*" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 focus:outline-none">
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
                                        <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
                                            <h4 class="text-sm font-bold text-slate-100 mb-4">Add Material to "{{ $module->title }}"</h4>
                                            <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                @csrf
                                                <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Material Title</label>
                                                    <input name="title" type="text" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Document File</label>
                                                    <input name="material_file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 focus:outline-none">
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
                        @empty
                            <div class="text-center py-12 text-slate-500 border border-dashed border-slate-850 rounded-2xl">No modules created yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- Teacher Curriculum Builder panel (Add Module) -->
            @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                    <h3 class="text-base font-bold text-slate-200 mb-4">Add Curriculum Chapter</h3>
                    <form action="{{ route('modules.store') }}" method="POST" class="flex items-center space-x-3">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <div class="flex-1">
                            <input name="title" type="text" required placeholder="e.g. Chapter 1: Anatomy Fundamentals" class="w-full px-4 py-2.5 text-sm rounded-2xl bg-slate-900 border border-slate-800 text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-2xl transition-colors">
                            Add Module
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Live Classes Scheduling & Information Panel -->
        <div class="space-y-6">

            <!-- Upcoming Live Classes -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col">
                <h3 class="text-base font-bold text-slate-200 mb-1">Live Interactive Classes</h3>
                <p class="text-xs text-slate-400 mb-4">Google Meet study group meetings</p>

                @if(Auth::user()->isStudent() && !$isEnrolled)
                    <div class="text-center py-8 text-xs text-slate-600">Enroll in course to see live links.</div>
                @else
                    <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                        @forelse($course->liveClasses as $class)
                            <div class="p-4 bg-slate-900/40 border border-slate-850 rounded-2xl space-y-2">
                                <div class="flex justify-between items-start">
                                    <h5 class="text-xs font-bold text-slate-200 truncate pr-2">{{ $class->title }}</h5>
                                    @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                                        <form action="{{ route('live-classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Cancel this class session?')">
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
                                <div class="flex items-center justify-between text-[10px] text-slate-400">
                                    <span class="bg-indigo-500/10 text-indigo-400 px-1.5 py-0.5 rounded border border-indigo-500/20">
                                        {{ $class->datetime->format('M d, H:i') }}
                                    </span>
                                    <a href="{{ $class->link }}" target="_blank" class="px-2 py-1 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded">
                                        Join Meeting
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-xs text-slate-600">No live classes scheduled yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- Schedule a Live Class Form (Teachers/Admins only) -->
            @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                    <h3 class="text-base font-bold text-slate-200 mb-1">Schedule Live Class</h3>
                    <p class="text-xs text-slate-400 mb-4">Post Google Meet study slots</p>

                    <form action="{{ route('live-classes.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Class Title</label>
                            <input name="title" type="text" required placeholder="e.g. Clinical Q&A Workshop" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Date & Time</label>
                            <input name="datetime" type="datetime-local" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 focus:outline-none focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Google Meet Link</label>
                            <input name="link" type="url" required placeholder="https://meet.google.com/..." class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>

                        <button type="submit" class="w-full py-2 px-3 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all duration-200 shadow-md shadow-brand-600/10">
                            Schedule Class
                        </button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>

<!-- Vanilla JS Modal Controller -->
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
</script>
@endsection
