@extends('layouts.app')

@section('page_title', 'User Audit Profile')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-100 font-Outfit">User Audit Profile</h2>
            <p class="text-xs text-slate-400 mt-1">Review detailed activity trails, login histories, and enrollments for this user.</p>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-850 text-slate-350 border border-slate-800 hover:border-slate-700 rounded-xl text-xs font-bold transition-all duration-200 inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Directory</span>
            </a>
        </div>
    </div>

    <!-- Profile details Card -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-500/10 text-brand-400 font-Outfit text-xl font-bold flex items-center justify-center border border-brand-500/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h3 class="text-lg font-bold text-slate-100">{{ $user->name }}</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                        @if($user->isAdmin()) bg-rose-500/15 text-rose-400 border border-rose-500/30
                        @elseif($user->isTeacher()) bg-amber-500/15 text-amber-400 border border-amber-500/30
                        @else bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 @endif">
                        {{ $user->role }}
                    </span>
                </div>
                <p class="text-xs text-slate-450 mt-1">{{ $user->email }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 text-xs font-semibold">
            <div class="px-4 py-2 bg-slate-900/60 border border-slate-850 rounded-2xl">
                <span class="text-slate-500">School Batch:</span>
                <span class="text-slate-350 ml-1">{{ $user->schoolClass ? $user->schoolClass->name : 'No Class' }}</span>
            </div>
            <div class="px-4 py-2 bg-slate-900/60 border border-slate-850 rounded-2xl">
                <span class="text-slate-500">Status:</span>
                <span class="{{ $user->is_approved ? 'text-emerald-400' : 'text-rose-500' }} ml-1">
                    {{ $user->is_approved ? 'Approved' : 'Suspended' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Audited metrics panel -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Courses List Card -->
        <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg h-[400px] flex flex-col">
            <h3 class="text-sm font-bold text-slate-200 mb-4 border-b border-slate-850 pb-2 font-Outfit">
                @if($user->isStudent()) Enrolled Courses @else Assigned Courses @endif
            </h3>
            <div class="overflow-y-auto flex-1 space-y-3 pr-1">
                @if($user->isStudent())
                    @forelse($enrolledCourses as $course)
                        <div class="p-3 bg-slate-900/50 border border-slate-850 rounded-2xl flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-200 truncate max-w-[150px]">{{ $course->title }}</h4>
                                <span class="text-[9px] text-slate-500 block mt-0.5">Duration: {{ $course->duration ?? 'N/A' }}</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Approved
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-12">No course enrollments found.</p>
                    @endforelse
                @else
                    @forelse($taughtCourses as $course)
                        <div class="p-3 bg-slate-900/50 border border-slate-850 rounded-2xl flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-200 truncate max-w-[150px]">{{ $course->title }}</h4>
                                <span class="text-[9px] text-slate-500 block mt-0.5">Duration: {{ $course->duration ?? 'N/A' }}</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                Assigned
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-12">No course assignments found.</p>
                    @endforelse
                @endif
            </div>
        </div>

        <!-- Audit Tabs Area (Login history & usage actions) -->
        <div class="md:col-span-2 bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg h-[400px] flex flex-col">
            <!-- Tabs Headers -->
            <div class="flex border-b border-slate-800 mb-4">
                <button onclick="switchTab('logins')" id="tab-logins" class="px-4 py-2 text-xs font-bold border-b-2 border-brand-500 text-brand-400 focus:outline-none transition-colors">
                    Login History ({{ count($loginHistories) }})
                </button>
                <button onclick="switchTab('activities')" id="tab-activities" class="px-4 py-2 text-xs font-bold border-b-2 border-transparent text-slate-400 hover:text-slate-200 focus:outline-none transition-colors">
                    Learning Actions ({{ count($usageLogs) }})
                </button>
            </div>

            <!-- Login logs Tab Content -->
            <div id="content-logins" class="flex-1 overflow-y-auto pr-1">
                <table class="w-full text-left text-xs text-slate-350">
                    <thead class="bg-slate-900 text-[10px] text-slate-400 uppercase tracking-wider sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 rounded-l-xl">Log Time</th>
                            <th class="px-4 py-2.5">IP Address</th>
                            <th class="px-4 py-2.5 rounded-r-xl">User Agent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                        @forelse($loginHistories as $log)
                            <tr class="hover:bg-slate-900/20 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-350">{{ $log->logged_in_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-400">{{ $log->ip_address }}</td>
                                <td class="px-4 py-3 text-[10px] text-slate-500 truncate max-w-[200px]" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-12 text-slate-500">No login history recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Action logs Tab Content -->
            <div id="content-activities" class="flex-1 overflow-y-auto pr-1 hidden">
                <table class="w-full text-left text-xs text-slate-350">
                    <thead class="bg-slate-900 text-[10px] text-slate-400 uppercase tracking-wider sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 rounded-l-xl">Action Type</th>
                            <th class="px-4 py-2.5">Course Scope</th>
                            <th class="px-4 py-2.5">Details</th>
                            <th class="px-4 py-2.5 rounded-r-xl">Logged At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                        @forelse($usageLogs as $act)
                            <tr class="hover:bg-slate-900/20 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-250">
                                    <span class="px-2 py-0.5 rounded text-[8px] uppercase tracking-wider
                                        @if($act->action === 'watch_video') bg-brand-500/10 text-brand-400 border border-brand-500/20
                                        @else bg-sky-500/10 text-sky-400 border border-sky-500/20 @endif">
                                        {{ str_replace('_', ' ', $act->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 font-medium truncate max-w-[120px]">{{ $act->course ? $act->course->title : 'N/A' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $act->details }}</td>
                                <td class="px-4 py-3 text-[10px] text-slate-500">{{ $act->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-slate-500">No learning activity logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>

<!-- Tab Swapper script -->
<script>
    function switchTab(tab) {
        const loginsBtn = document.getElementById('tab-logins');
        const activitiesBtn = document.getElementById('tab-activities');
        const loginsContent = document.getElementById('content-logins');
        const activitiesContent = document.getElementById('content-activities');

        if (tab === 'logins') {
            loginsBtn.className = "px-4 py-2 text-xs font-bold border-b-2 border-brand-500 text-brand-400 focus:outline-none transition-colors";
            activitiesBtn.className = "px-4 py-2 text-xs font-bold border-b-2 border-transparent text-slate-400 hover:text-slate-200 focus:outline-none transition-colors";
            loginsContent.classList.remove('hidden');
            activitiesContent.classList.add('hidden');
        } else {
            activitiesBtn.className = "px-4 py-2 text-xs font-bold border-b-2 border-brand-500 text-brand-400 focus:outline-none transition-colors";
            loginsBtn.className = "px-4 py-2 text-xs font-bold border-b-2 border-transparent text-slate-400 hover:text-slate-200 focus:outline-none transition-colors";
            activitiesContent.classList.remove('hidden');
            loginsContent.classList.add('hidden');
        }
    }
</script>
@endsection
