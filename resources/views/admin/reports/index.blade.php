@extends('layouts.app')

@section('page_title', 'Activity Reports & Logs')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">

    <!-- Header -->
    <div>
        <h2 class="text-xl font-bold text-slate-100 font-Outfit">Audit Trails & Usage Logs</h2>
        <p class="text-xs text-slate-400 mt-1">Review student learning progress, file downloads, video streaming stats, and user login activity</p>
    </div>

    <!-- Metrics Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total User Logins</p>
                <h3 class="text-2xl font-extrabold text-slate-200 mt-1.5">{{ $stats['total_logins'] }}</h3>
            </div>
            <div class="p-2.5 bg-indigo-500/10 text-indigo-400 rounded-xl">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h5a3 3 0 013 3v1" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Actions logged</p>
                <h3 class="text-2xl font-extrabold text-slate-200 mt-1.5">{{ $stats['total_activities'] }}</h3>
            </div>
            <div class="p-2.5 bg-brand-500/10 text-brand-400 rounded-xl">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Video Stream Views</p>
                <h3 class="text-2xl font-extrabold text-slate-200 mt-1.5">{{ $stats['video_watches'] }}</h3>
            </div>
            <div class="p-2.5 bg-emerald-500/10 text-emerald-400 rounded-xl">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PDF Downloads</p>
                <h3 class="text-2xl font-extrabold text-slate-200 mt-1.5">{{ $stats['material_downloads'] }}</h3>
            </div>
            <div class="p-2.5 bg-amber-500/10 text-amber-400 rounded-xl">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Reports Navigation Tabs -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg space-y-6">
        
        <!-- Tab Headers -->
        <div class="flex border-b border-slate-800">
            <button onclick="switchTab('usage-tab')" id="btn-usage-tab" class="px-6 py-3 border-b-2 border-brand-500 text-sm font-bold text-slate-100 focus:outline-none transition-all duration-200">
                Activity & Usage Logs
            </button>
            <button onclick="switchTab('login-tab')" id="btn-login-tab" class="px-6 py-3 border-b-2 border-transparent text-sm font-medium text-slate-400 hover:text-slate-200 focus:outline-none transition-all duration-200">
                User Login Histories
            </button>
            <button onclick="switchTab('progress-tab')" id="btn-progress-tab" class="px-6 py-3 border-b-2 border-transparent text-sm font-medium text-slate-400 hover:text-slate-200 focus:outline-none transition-all duration-200">
                Student Progress Profiles
            </button>
        </div>

        <!-- Tab 1: Usage/Activity Logs -->
        <div id="usage-tab" class="tab-content space-y-4">
            <h4 class="text-sm font-bold text-slate-200">Student Learning Logs</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-350 border-collapse">
                    <thead class="bg-slate-900/60 text-slate-450 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 rounded-l-xl">User Name</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Course Context</th>
                            <th class="px-4 py-3">Action performed</th>
                            <th class="px-4 py-3">Action Description</th>
                            <th class="px-4 py-3 rounded-r-xl text-right">Logged At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                        @forelse($usageLogs as $log)
                            <tr class="hover:bg-slate-900/10">
                                <td class="px-4 py-3.5 font-bold text-slate-300">
                                    {{ $log->user->name }}
                                    <span class="block text-[10px] text-slate-500 font-normal mt-0.5">{{ $log->user->email }}</span>
                                </td>
                                <td class="px-4 py-3.5 capitalize font-medium text-slate-400">{{ $log->user->role }}</td>
                                <td class="px-4 py-3.5 font-semibold text-brand-400">
                                    {{ $log->course ? $log->course->title : 'N/A' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-bold uppercase tracking-wider text-[9px]
                                        @if($log->action === 'watch_video') bg-emerald-500/15 text-emerald-400 border border-emerald-500/25
                                        @else bg-amber-500/15 text-amber-400 border border-amber-500/25 @endif">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-400 font-medium max-w-xs truncate">{{ $log->details }}</td>
                                <td class="px-4 py-3.5 text-right font-medium text-slate-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-500">No learning activity logged yet. When students watch videos or download study guides, data logs will populate here.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Student Progress Profiles -->
        <div id="progress-tab" class="tab-content space-y-4 hidden">
            <h4 class="text-sm font-bold text-slate-200">Student Progress Profiles</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-350 border-collapse">
                    <thead class="bg-slate-900/60 text-slate-450 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 rounded-l-xl">Student Name</th>
                            <th class="px-4 py-3">Class Batch</th>
                            <th class="px-4 py-3">Active Enrollments</th>
                            <th class="px-4 py-3">Lecture Progress</th>
                            <th class="px-4 py-3">Total Watch Time</th>
                            <th class="px-4 py-3 rounded-r-xl text-right">Live Class Attendance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                        @forelse($studentAnalytics as $item)
                            <tr class="hover:bg-slate-900/10">
                                <td class="px-4 py-3.5 font-bold text-slate-300">
                                    <a href="{{ route('admin.users.profile', $item['student']->id) }}" class="hover:text-brand-400 transition-colors">{{ $item['student']->name }}</a>
                                    <span class="block text-[10px] text-slate-500 font-normal mt-0.5">{{ $item['student']->email }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-400 font-medium">
                                    {{ $item['student']->schoolClass ? $item['student']->schoolClass->name : 'No Class' }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-400 font-medium">
                                    @if(count($item['courses']) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($item['courses'] as $cTitle)
                                                <span class="px-2 py-0.5 rounded text-[9px] bg-brand-500/10 text-brand-400 border border-brand-500/15 max-w-[120px] truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-650 italic">Not Enrolled</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 bg-slate-900 rounded-full h-1.5 border border-slate-850">
                                            <div class="bg-brand-505 h-1.5 rounded-full" style="width: {{ $item['progress_percent'] }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-300">{{ $item['progress_percent'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-400 font-semibold">{{ $item['watch_hours'] }} Hrs</td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border 
                                        @if($item['attendance_rate'] >= 75) bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                        @elseif($item['attendance_rate'] >= 40) bg-amber-500/10 text-amber-400 border-amber-500/20
                                        @else bg-rose-500/10 text-rose-400 border-rose-500/20 @endif">
                                        {{ $item['attendance_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-500">No student users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: User Login History -->
        <div id="login-tab" class="tab-content space-y-4 hidden">
            <h4 class="text-sm font-bold text-slate-200">User Authentication Logs</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-350 border-collapse">
                    <thead class="bg-slate-900/60 text-slate-450 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 rounded-l-xl">User Name</th>
                            <th class="px-4 py-3">User Email</th>
                            <th class="px-4 py-3">Role Badge</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">Browser / User Agent</th>
                            <th class="px-4 py-3 rounded-r-xl text-right">Login timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                        @forelse($loginHistories as $login)
                            <tr class="hover:bg-slate-900/10">
                                <td class="px-4 py-3.5 font-bold text-slate-300">{{ $login->user->name }}</td>
                                <td class="px-4 py-3.5 text-slate-400 font-medium">{{ $login->user->email }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                        @if($login->user->isAdmin()) bg-rose-500/10 text-rose-400 border border-rose-500/20
                                        @elseif($login->user->isTeacher()) bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 @endif">
                                        {{ $login->user->role }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-slate-350">{{ $login->ip_address }}</td>
                                <td class="px-4 py-3.5 text-slate-500 font-normal max-w-sm truncate" title="{{ $login->user_agent }}">
                                    {{ $login->user_agent }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-medium text-slate-500">{{ $login->logged_in_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-500">No login events captured in database yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- Tab switches script -->
<script>
    function switchTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Show selected tab content
        document.getElementById(tabId).classList.remove('hidden');

        // Reset all button styles
        document.querySelectorAll('[id^="btn-"]').forEach(btn => {
            btn.classList.remove('border-brand-500', 'text-slate-100', 'font-bold');
            btn.classList.add('border-transparent', 'text-slate-400', 'font-medium');
        });

        // Set active button style
        const activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.add('border-brand-500', 'text-slate-100', 'font-bold');
        activeBtn.classList.remove('border-transparent', 'text-slate-400', 'font-medium');
    }
</script>
@endsection
