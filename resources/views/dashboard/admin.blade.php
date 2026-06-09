@extends('layouts.app')

@section('page_title', 'Admin Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Users</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['total_users'] }}</h3>
            </div>
            <div class="p-3 bg-brand-500/10 text-brand-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Courses</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['total_courses'] }}</h3>
            </div>
            <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Teachers</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['total_teachers'] }}</h3>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <div class="bg-slate-950/40 border border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-lg">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Students</p>
                <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ $stats['total_students'] }}</h3>
            </div>
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Admin Controls Panel -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- User Management Table -->
        <div class="xl:col-span-2 bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-100">User Role Management</h3>
                    <p class="text-xs text-slate-400 mt-1">Assign teachers and manage student permissions</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900 text-xs text-slate-400 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 rounded-l-2xl">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Current Role</th>
                            <th class="px-4 py-3 rounded-r-2xl text-right">Assign Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($users as $user)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="px-4 py-4 font-semibold text-slate-200">{{ $user->name }}</td>
                            <td class="px-4 py-4 text-xs">{{ $user->email }}</td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    @if($user->isAdmin()) bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @elseif($user->isTeacher()) bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 @endif">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('admin.users.update-role', $user->id) }}" method="POST" class="inline-flex items-center space-x-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()" class="bg-slate-900 border border-slate-800 text-xs rounded-xl px-2.5 py-1.5 text-slate-300 focus:outline-none focus:border-brand-500 cursor-pointer">
                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Panel Column -->
        <div class="flex flex-col">
            
            <!-- Create User Accounts (Admin Only) -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg mb-6">
                <h3 class="text-base font-bold text-slate-100 mb-1">Create User Account</h3>
                <p class="text-xs text-slate-400 mb-4">Register new Teachers, Students or Admins</p>

                <form action="{{ route('admin.users.create') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Full Name</label>
                        <input name="name" type="text" required placeholder="e.g. Dr. Sarah Connor" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-250 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Address</label>
                        <input name="email" type="email" required placeholder="name@lms.com" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-250 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Password</label>
                        <input name="password" type="password" required placeholder="••••••••" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-250 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assign Role</label>
                        <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-250 focus:outline-none focus:border-brand-500">
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                        Register User
                    </button>
                </form>
            </div>

            <!-- Course Catalog Overview -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-100">All Courses</h3>
                    <p class="text-xs text-slate-400 mt-1">Status of created courses</p>
                </div>
                <a href="{{ route('courses.create') }}" class="text-xs font-bold text-brand-400 hover:text-brand-300 transition-colors">+ New Course</a>
            </div>

            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
                @forelse($courses as $course)
                <div class="p-4 bg-slate-900/50 border border-slate-850 rounded-2xl flex items-center justify-between hover:border-slate-800 transition-colors">
                    <div class="min-w-0 pr-2">
                        <h4 class="text-sm font-bold text-slate-200 truncate">{{ $course->title }}</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">Teacher: {{ $course->teacher->name }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border flex-shrink-0
                        @if($course->is_published) bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                        @else bg-slate-800 text-slate-500 border-slate-800/30 @endif">
                        {{ $course->is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-500">No courses created yet.</div>
                @endforelse
            </div>
        </div>
        </div>

    </div>

</div>
@endsection
