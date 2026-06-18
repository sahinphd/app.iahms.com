@extends('layouts.app')

@section('page_title', 'Course Catalog')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-100">Explore Courses</h2>
            <p class="text-xs text-slate-400 mt-1">Acquire new skills from paramedical and medical training pathways</p>
        </div>
        @if(Auth::user()->isAdmin() || Auth::user()->isTeacher())
            <a href="{{ route('courses.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all duration-200 shadow-lg shadow-brand-500/10">
                + Create Course
            </a>
        @endif
    </div>

    <!-- Course Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($courses as $course)
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-5 hover:border-slate-750 transition-all duration-200 flex flex-col justify-between">
                
                <div>
                    <!-- Thumbnail Container -->
                    <div class="h-44 w-full rounded-2xl bg-slate-900 overflow-hidden mb-4 border border-slate-800/80 relative">
                        @if($course->thumbnail)
                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex flex-col items-center justify-center text-slate-700 font-bold bg-slate-950">
                                <svg class="h-10 w-10 text-slate-750 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="text-xs tracking-wider uppercase font-extrabold text-slate-700">Course Info</span>
                            </div>
                        @endif
                        
                        <!-- Role Badge / Publish Badge -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isTeacher())
                            <span class="absolute top-3 right-3 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded border 
                                @if($course->is_published) bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                @else bg-slate-950 text-slate-400 border-slate-800 @endif">
                                {{ $course->is_published ? 'Published' : 'Draft' }}
                            </span>
                        @endif
                    </div>

                    <!-- Title & Teacher -->
                    <h3 class="text-base font-bold text-slate-200 truncate">{{ $course->title }}</h3>
                    <p class="text-[10px] text-slate-400 mt-1 font-medium">Instructor: {{ $course->teacher->name }}</p>
                    <p class="text-xs text-slate-500 mt-3 line-clamp-2">{{ $course->description }}</p>
                </div>

                <!-- Footer Actions -->
                <div class="mt-6 pt-4 border-t border-slate-900 flex items-center justify-between">
                    <a href="{{ route('courses.show', $course->id) }}" class="text-xs font-bold text-slate-400 hover:text-slate-200 transition-colors">
                        View Syllabus &rarr;
                    </a>

                    <!-- Student Enrollment Buttons -->
                    @if(Auth::user()->isStudent())
                        @if($course->school_class_id && $course->school_class_id == Auth::user()->school_class_id)
                            <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold select-none flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Enrolled (Class)</span>
                            </span>
                        @elseif(array_key_exists($course->id, $enrollmentsMap))
                            @if($enrollmentsMap[$course->id])
                                <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold select-none flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Enrolled</span>
                                </span>
                            @else
                                <span class="px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-bold select-none flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Pending Approval</span>
                                </span>
                            @endif
                        @else
                            <form action="{{ route('enrollments.enroll') }}" method="POST">
                                @csrf
                                <input type="hidden" name="course_id" value="{{ $course->id }}">
                                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                                    Enroll Course
                                </button>
                            </form>
                        @endif
                    @endif

                    <!-- Teacher Edit actions -->
                    @if(Auth::user()->isAdmin() || (Auth::user()->isTeacher() && $course->teacher_id === Auth::id()))
                        <div class="flex items-center space-x-1.5">
                            <a href="{{ route('courses.edit', $course->id) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-800 hover:bg-slate-900 text-[10px] font-semibold text-slate-300 transition-colors">
                                Edit
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-full text-center py-16 text-slate-500 border border-dashed border-slate-850 rounded-3xl bg-slate-900/10">
                <p class="text-sm font-medium">No courses available at the moment.</p>
                @if(Auth::user()->isAdmin() || Auth::user()->isTeacher())
                    <a href="{{ route('courses.create') }}" class="inline-block mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs transition-colors">
                        Create Your First Course
                    </a>
                @endif
            </div>
        @endforelse
    </div>

</div>
@endsection
