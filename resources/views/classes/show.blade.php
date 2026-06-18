@extends('layouts.app')

@section('page_title', 'Class Details - ' . $schoolClass->name)

@section('content')
<div class="space-y-8 max-w-6xl mx-auto animate-fadeIn">

    <!-- Class Header Banner -->
    <div class="relative bg-slate-950/60 border border-slate-800 rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row gap-6 items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 bg-brand-500/10 text-brand-400 rounded-2xl border border-brand-500/20 overflow-hidden flex-shrink-0 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-500/20 text-brand-400 border border-brand-500/20">
                            Classroom / Batch
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-350 border border-slate-750">
                            {{ $schoolClass->students->count() }} Students
                        </span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight leading-tight font-Outfit">{{ $schoolClass->name }}</h2>
                    <p class="text-xs md:text-sm text-slate-400 max-w-2xl leading-relaxed">{{ $schoolClass->description ?: 'No description provided.' }}</p>
                </div>
            </div>
            
            <div>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-slate-200 border border-slate-700/60 hover:border-slate-700 rounded-xl text-xs font-semibold transition-all duration-200">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Layout Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Roster & Courses (Left 2/3) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Enrolled Students List -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-200 font-Outfit">Student Roster</h3>
                    <p class="text-xs text-slate-400 mt-0.5">List of students currently allotted to this batch</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-850 text-slate-450 font-bold uppercase tracking-wider text-[10px]">
                                <th class="pb-3 pl-2">Name</th>
                                <th class="pb-3">Email</th>
                                <th class="pb-3 text-right pr-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850/60 text-slate-300">
                            @forelse($schoolClass->students as $student)
                                <tr class="hover:bg-slate-900/30 transition-colors">
                                    <td class="py-3 pl-2 font-semibold text-slate-200">{{ $student->name }}</td>
                                    <td class="py-3 text-slate-400">{{ $student->email }}</td>
                                    <td class="py-3 text-right pr-2">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $student->is_approved ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                            {{ $student->is_approved ? 'Approved' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-500 italic">No students enrolled in this class yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Associated Courses/Subjects -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                <div>
                    <h3 class="text-base font-bold text-slate-200 font-Outfit">Course Curricula</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Syllabus sections mapped to this class</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($schoolClass->courses as $course)
                        <div class="p-4 bg-slate-900/40 border border-slate-850 rounded-2xl flex flex-col justify-between hover:border-slate-800 transition-colors">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-slate-250 truncate">{{ $course->title }}</h4>
                                    <span class="px-1.5 py-0.5 text-[8px] font-bold {{ $course->is_completed ? 'bg-emerald-500/25 text-emerald-400' : 'bg-brand-500/20 text-brand-400' }} rounded uppercase">
                                        {{ $course->is_completed ? 'Completed' : 'Active' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-2 line-clamp-2">{{ $course->description }}</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-850/60 flex items-center justify-between text-[9px] text-slate-450 uppercase font-bold">
                                <span>Duration: {{ $course->duration ?: 'Self-paced' }}</span>
                                <a href="{{ route('courses.show', $course->id) }}" class="text-brand-400 hover:text-brand-300 font-bold transition-colors">Syllabus &rarr;</a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-slate-500 italic text-xs">No courses mapped to this class yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Administration Panel (Right 1/3) -->
        <div class="space-y-6">

            <!-- Class Administration List -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-4">
                <h3 class="text-base font-bold text-slate-200 font-Outfit">Class Administration</h3>
                <p class="text-xs text-slate-400">Assigned Class Administrators and Teachers</p>

                <div class="space-y-3">
                    @forelse($schoolClass->teachers as $teacher)
                        <div class="flex items-center justify-between p-3 bg-slate-900/40 border border-slate-850 rounded-2xl">
                            <div>
                                <p class="text-xs font-bold text-slate-200">{{ $teacher->name }}</p>
                                <p class="text-[9px] text-slate-500">{{ $teacher->email }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase {{ $teacher->pivot->role === 'class_admin' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-850 text-slate-450 border border-slate-800' }}">
                                {{ $teacher->pivot->role === 'class_admin' ? 'Class Admin' : 'Teacher' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic">No teachers assigned to this class yet.</p>
                    @endforelse
                </div>

                @if($canManageClass)
                    <!-- Class Teacher Sync Roster Form -->
                    <div class="pt-4 border-t border-slate-850/60 space-y-3">
                        <h4 class="text-xs font-bold text-slate-350">Sync Class Teachers</h4>
                        <form action="{{ route('admin.classes.assign-teachers', $schoolClass->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-1 bg-slate-900/20 p-2.5 rounded-2xl border border-slate-850">
                                @foreach($allTeachers as $teacher)
                                    @php
                                        $assigned = $schoolClass->teachers->firstWhere('id', $teacher->id);
                                        $currentRole = $assigned ? $assigned->pivot->role : 'teacher';
                                    @endphp
                                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-850">
                                        <label class="flex items-center space-x-2 cursor-pointer text-xs text-slate-300 hover:text-slate-100 min-w-0 flex-1">
                                            <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}" {{ $assigned ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-900 text-brand-600 focus:ring-brand-500">
                                            <span class="truncate ml-1.5">{{ $teacher->name }}</span>
                                        </label>
                                        <select name="roles[{{ $teacher->id }}]" class="bg-slate-900 border border-slate-800 rounded-lg text-[9px] text-slate-300 py-1 px-1.5 focus:outline-none focus:border-brand-500 cursor-pointer">
                                            <option value="class_admin" {{ $currentRole === 'class_admin' ? 'selected' : '' }}>Class Admin</option>
                                            <option value="teacher" {{ $currentRole === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit" class="w-full py-2.5 px-3 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all duration-200 shadow-md shadow-brand-600/10 uppercase tracking-wider">
                                Sync Class Roster
                            </button>
                        </form>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
