@extends('layouts.app')

@section('page_title', 'School Class Setup')

@section('content')
<div class="space-y-8">

    <!-- Header Actions -->
    <div>
        <h2 class="text-xl font-bold text-slate-100 font-Outfit">Manage School Classes & Batches</h2>
        <p class="text-xs text-slate-400 mt-1">Create classrooms/batches, assign students, and configure multi-teacher class administrations</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Classes List (Left 2/3) -->
        <div class="lg:col-span-2 bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-200">Active Classes / Batches</h3>
                <p class="text-xs text-slate-400 mt-0.5">List of structured class groupings, subjects completion rates, and rosters</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($classes as $class)
                    @php
                        $classCourses = $class->courses;
                        $totalCourses = $classCourses->count();
                        $completedCourses = $classCourses->where('is_completed', true)->count();
                        $percent = $totalCourses > 0 ? round(($completedCourses / $totalCourses) * 100) : 0;
                    @endphp
                    <div class="p-5 bg-slate-900/50 border border-slate-800 rounded-2xl flex flex-col justify-between hover:border-slate-750 transition-colors relative">
                        <div class="space-y-3">
                            <!-- Class title & Delete -->
                            <div class="flex justify-between items-start">
                                <h4 class="text-sm font-bold text-slate-200 flex items-center space-x-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                                    <span>{{ $class->name }}</span>
                                </h4>
                                <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Delete this class? Members will be unassigned.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-500 hover:text-rose-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Description -->
                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $class->description ?: 'No description provided.' }}</p>

                            <!-- Syllabus Progress Bar -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[10px] font-bold text-slate-450 uppercase tracking-wider">
                                    <span>Batch Syllabus Completed</span>
                                    <span>{{ $completedCourses }}/{{ $totalCourses }} Subjects ({{ $percent }}%)</span>
                                </div>
                                <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden border border-slate-850">
                                    <div class="bg-brand-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>

                            <!-- Assigned Teachers List -->
                            <div class="space-y-1.5 pt-2 border-t border-slate-850">
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Teachers Assigned</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($class->teachers as $t)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-950 text-[9px] font-bold text-slate-350 border border-slate-850" title="Role: {{ $t->pivot->role }}">
                                            {{ $t->name }} ({{ $t->pivot->role === 'class_admin' ? 'Class Admin' : 'Teacher' }})
                                        </span>
                                    @empty
                                        <span class="text-[9px] text-slate-500 italic">No assigned teachers.</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Assign Teachers Form (Admin Only) -->
                            @if(Auth::user()->isAdmin())
                                <form action="{{ route('admin.classes.assign-teachers', $class->id) }}" method="POST" class="pt-3 border-t border-slate-850 space-y-2">
                                    @csrf
                                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Manage Class Administration</span>
                                    <div class="max-h-24 overflow-y-auto space-y-1.5 bg-slate-950/30 p-2 rounded-xl border border-slate-850/50">
                                        @foreach($allTeachers as $t)
                                            @php
                                                $assigned = $class->teachers->firstWhere('id', $t->id);
                                            @endphp
                                            <div class="flex items-center justify-between text-[10px]">
                                                <label class="flex items-center space-x-1.5 cursor-pointer text-slate-350 hover:text-slate-100">
                                                    <input type="checkbox" name="teachers[]" value="{{ $t->id }}" {{ $assigned ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-950 text-brand-600 focus:ring-brand-500 w-3 h-3">
                                                    <span class="truncate max-w-[100px]">{{ $t->name }}</span>
                                                </label>
                                                <select name="roles[{{ $t->id }}]" class="bg-slate-900 border border-slate-800 text-[9px] rounded px-1 py-0.5 text-slate-300 focus:outline-none focus:border-brand-500">
                                                    <option value="teacher" {{ $assigned && $assigned->pivot->role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                    <option value="class_admin" {{ $assigned && $assigned->pivot->role === 'class_admin' ? 'selected' : '' }}>Class Admin</option>
                                                </select>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="w-full py-1 bg-slate-950 hover:bg-slate-900 border border-slate-850 hover:border-slate-800 rounded-lg text-[9px] font-bold text-brand-400 hover:text-brand-300 uppercase transition-all duration-200">
                                        Save Class Roster
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-850 flex justify-between items-center text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                            <span>{{ $class->students_count }} Students</span>
                            <span>{{ $class->courses_count }} Courses</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-slate-500 border border-dashed border-slate-850 rounded-2xl">
                        No school classes created yet. Use the panel on the right to add one.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Create Class Form (Right 1/3) -->
        <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg h-fit">
            <h3 class="text-base font-bold text-slate-200 mb-1">Create New Class</h3>
            <p class="text-xs text-slate-400 mb-4">Set up a new grade, batch or study section</p>

            <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Class Name</label>
                    <input name="name" type="text" required placeholder="e.g. Class A, Grade 10" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the target student group..." class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                    Create Class
                </button>
            </form>
        </div>

    </div>

    <!-- User Assignment Controls -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-6">
        <div>
            <h3 class="text-base font-bold text-slate-200">Allot Students & Teachers to Classes</h3>
            <p class="text-xs text-slate-400 mt-0.5">Separate workflows for student classroom assignments and teacher multi-batch rosters</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Classwise Student Allotment -->
            <div class="p-5 bg-slate-900/20 border border-slate-850 rounded-2xl space-y-4">
                <div class="space-y-1">
                    <h4 class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Classwise Student Allotment</h4>
                    <p class="text-[10px] text-slate-500">Select a class, check the students to allot, and save.</p>
                </div>

                <form action="{{ route('admin.classes.allot-students-bulk') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-455 uppercase tracking-wider mb-1">Select Target Class</label>
                        <select id="allot-class-select" name="school_class_id" required onchange="updateStudentSelection(this.value)" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                            <option value="" disabled selected>-- Choose Class --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="students-allotment-list" class="hidden space-y-3">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Check Students to Allot:</span>
                        <div class="overflow-y-auto max-h-[250px] pr-1 space-y-2 bg-slate-950/30 p-3 rounded-xl border border-slate-850/50">
                            @foreach($students as $student)
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900/40 border border-slate-850">
                                    <label class="flex items-center space-x-2.5 text-xs text-slate-200 cursor-pointer w-full py-1">
                                        <input type="checkbox" name="students[]" value="{{ $student->id }}" data-current-class="{{ $student->school_class_id }}" class="student-allot-checkbox rounded border-slate-800 bg-slate-950 text-brand-600 focus:ring-brand-500 w-4 h-4">
                                        <div class="min-w-0 ml-2">
                                            <span class="font-bold block text-slate-250">{{ $student->name }}</span>
                                            <span class="text-[9px] text-slate-500">{{ $student->email }}</span>
                                        </div>
                                    </label>
                                    <span id="current-class-badge-{{ $student->id }}" class="px-2 py-0.5 rounded text-[8px] font-bold uppercase border border-slate-800 bg-slate-950 text-slate-450 whitespace-nowrap">
                                        {{ $student->schoolClass ? $student->schoolClass->name : 'No Class' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="submit" class="w-full py-2 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all shadow-md shadow-brand-600/10">
                            Save Student Allotments
                        </button>
                    </div>
                </form>
            </div>

            <!-- Classwise Teacher Allotment -->
            <div class="p-5 bg-slate-900/20 border border-slate-850 rounded-2xl space-y-4">
                <div class="space-y-1">
                    <h4 class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Classwise Teacher Allotment</h4>
                    <p class="text-[10px] text-slate-500">Select a class, check the teachers to allot with their roles, and save.</p>
                </div>

                <form id="teachers-allotment-form" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-455 uppercase tracking-wider mb-1">Select Target Class</label>
                        <select id="allot-teacher-class-select" name="school_class_id" required onchange="updateTeacherSelection(this.value)" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                            <option value="" disabled selected>-- Choose Class --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="teachers-allotment-list" class="hidden space-y-3">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Check Teachers to Allot:</span>
                        <div class="overflow-y-auto max-h-[250px] pr-1 space-y-2 bg-slate-950/30 p-3 rounded-xl border border-slate-850/50">
                            @foreach($teachers as $teacher)
                                @php
                                    $assignments = [];
                                    foreach($teacher->assignedClasses as $ac) {
                                        $assignments[$ac->id] = $ac->pivot->role;
                                    }
                                @endphp
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900/40 border border-slate-850">
                                    <label class="flex items-center space-x-2.5 text-xs text-slate-250 cursor-pointer w-full py-1">
                                        <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}" 
                                               data-assignments='@json($assignments)' 
                                               class="teacher-allot-checkbox rounded border-slate-800 bg-slate-950 text-brand-600 focus:ring-brand-500 w-4 h-4">
                                        <div class="min-w-0 ml-2">
                                            <span class="font-bold block text-slate-250">{{ $teacher->name }}</span>
                                            <span class="text-[9px] text-slate-500">{{ $teacher->email }}</span>
                                            <span id="other-classes-{{ $teacher->id }}" class="text-[8px] text-slate-500 block font-normal mt-0.5"></span>
                                        </div>
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <select id="role-select-{{ $teacher->id }}" name="roles[{{ $teacher->id }}]" class="bg-slate-900 border border-slate-800 text-[10px] rounded-xl px-2 py-1 text-slate-300 focus:outline-none focus:border-brand-500 cursor-pointer">
                                            <option value="teacher">Teacher</option>
                                            <option value="class_admin">Class Admin</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="submit" class="w-full py-2 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all shadow-md shadow-brand-600/10 font-Outfit uppercase tracking-wider text-center">
                            Save Teacher Allotments
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

<script>
    function updateStudentSelection(classId) {
        const listContainer = document.getElementById('students-allotment-list');
        if (!classId) {
            listContainer.classList.add('hidden');
            return;
        }
        
        listContainer.classList.remove('hidden');
        
        // Get all checkboxes
        const checkboxes = document.querySelectorAll('.student-allot-checkbox');
        checkboxes.forEach(cb => {
            const currentClass = cb.getAttribute('data-current-class');
            if (currentClass == classId) {
                cb.checked = true;
            } else {
                cb.checked = false;
            }
        });
    }

    function updateTeacherSelection(classId) {
        const listContainer = document.getElementById('teachers-allotment-list');
        const form = document.getElementById('teachers-allotment-form');
        if (!classId) {
            listContainer.classList.add('hidden');
            return;
        }
        
        listContainer.classList.remove('hidden');
        form.action = `/admin/classes/${classId}/assign-teachers`;
        
        const checkboxes = document.querySelectorAll('.teacher-allot-checkbox');
        checkboxes.forEach(cb => {
            const teacherId = cb.value;
            const assignments = JSON.parse(cb.getAttribute('data-assignments') || '{}');
            const roleSelect = document.getElementById(`role-select-${teacherId}`);
            const otherClassesSpan = document.getElementById(`other-classes-${teacherId}`);
            
            // 1. Resolve role and checked state
            if (assignments.hasOwnProperty(classId)) {
                cb.checked = true;
                if (roleSelect) roleSelect.value = assignments[classId];
            } else {
                cb.checked = false;
                if (roleSelect) roleSelect.value = 'teacher';
            }
            
            // 2. Generate other classes list to help UI understanding
            const otherClassNames = [];
            const classSelect = document.getElementById('allot-teacher-class-select');
            const classNamesMap = {};
            for (let i = 0; i < classSelect.options.length; i++) {
                const opt = classSelect.options[i];
                if (opt.value) {
                    classNamesMap[opt.value] = opt.text;
                }
            }
            
            for (const cid in assignments) {
                if (cid != classId) {
                    const cname = classNamesMap[cid] || `Class #${cid}`;
                    const crole = assignments[cid] === 'class_admin' ? 'Class Admin' : 'Teacher';
                    otherClassNames.push(`${cname} (${crole})`);
                }
            }
            
            if (otherClassesSpan) {
                if (otherClassNames.length > 0) {
                    otherClassesSpan.textContent = `Also in: ${otherClassNames.join(', ')}`;
                    otherClassesSpan.classList.remove('hidden');
                } else {
                    otherClassesSpan.textContent = '';
                    otherClassesSpan.classList.add('hidden');
                }
            }
        });
    }
</script>
@endsection
