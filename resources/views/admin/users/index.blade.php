@extends('layouts.app')

@section('page_title', 'User Directory')

@section('content')
@php
    $caller = Auth::user();
    $canManageStudents = $caller->isAdmin() || $caller->hasPermission('manage_student_profiles');
    $canManageTeachers = $caller->isAdmin() || $caller->hasPermission('manage_teacher_profiles');
@endphp
<div class="space-y-8">

    <!-- Header info -->
    <div>
        <h2 class="text-xl font-bold text-slate-100 font-Outfit">User Directory Manager</h2>
        <p class="text-xs text-slate-400 mt-1">
            @if($caller->isAdmin())
                Manage and audit all student, teacher, and administrator accounts.
            @else
                Manage and audit accounts assigned to your profile management delegation.
            @endif
        </p>
    </div>

    <!-- Main controls grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- User list card -->
        <div class="xl:col-span-2 bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-100">Registered Users</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Toggle approval states and edit details</p>
                </div>
            </div>

            <!-- Search & Filter Controls -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-slate-900/30 border border-slate-850 p-4 rounded-2xl">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Search Users</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..." class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Filter by Role</label>
                    <select name="role" class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                        <option value="" class="bg-slate-900 text-slate-200">All Roles</option>
                        @if($caller->isAdmin())
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }} class="bg-slate-900 text-slate-200">Admin</option>
                        @endif
                        @if($canManageTeachers)
                            <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }} class="bg-slate-900 text-slate-200">Teacher</option>
                        @endif
                        @if($canManageStudents)
                            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }} class="bg-slate-900 text-slate-200">Student</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Filter by Class</label>
                    <select name="class_id" class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                        <option value="" class="bg-slate-900 text-slate-200">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }} class="bg-slate-900 text-slate-200">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Filter by Status</label>
                    <select name="status" class="w-full px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                        <option value="" class="bg-slate-900 text-slate-200">All Statuses</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }} class="bg-slate-900 text-slate-200">Approved</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }} class="bg-slate-900 text-slate-200">Pending</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-brand-600/10">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'role', 'class_id', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-350 hover:text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900 text-xs text-slate-400 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 rounded-l-2xl">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Class</th>
                            @if($caller->isAdmin())
                                <th class="px-4 py-3">Role Edit</th>
                                <th class="px-4 py-3">Permissions</th>
                            @endif
                            <th class="px-4 py-3 rounded-r-2xl text-right">Approve Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($users as $user)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="px-4 py-4 font-semibold text-slate-200">
                                <a href="{{ route('admin.users.profile', $user->id) }}" class="hover:text-brand-400 transition-colors">{{ $user->name }}</a>
                            </td>
                            <td class="px-4 py-4 text-xs">{{ $user->email }}</td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    @if($user->isAdmin()) bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @elseif($user->isTeacher()) bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 @endif">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    @if($user->is_approved) bg-emerald-500/15 text-emerald-400 border border-emerald-500/30
                                    @else bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse @endif">
                                    {{ $user->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs font-semibold text-slate-400">
                                {{ $user->schoolClass ? $user->schoolClass->name : 'No Class' }}
                            </td>
                            @if($caller->isAdmin())
                                <td class="px-4 py-4">
                                    <form action="{{ route('admin.users.update-role', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" onchange="this.form.submit()" class="bg-slate-900 border border-slate-800 text-xs rounded-xl px-2 py-1 text-slate-300 focus:outline-none focus:border-brand-500 cursor-pointer">
                                            <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-4">
                                    @if(!$user->isAdmin())
                                        <a href="{{ route('admin.permissions.user', $user->id) }}" class="px-2 py-1 bg-brand-500/10 hover:bg-brand-500 text-brand-400 hover:text-white border border-brand-500/20 hover:border-brand-500 rounded-lg text-[10px] font-bold uppercase transition-all duration-200">
                                            Customize
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-500 font-bold uppercase">Bypassed</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-4 text-right">
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.users.toggle-approval', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all duration-200 border 
                                            @if($user->is_approved) bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 border-rose-500/20
                                            @else bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-400 border-emerald-500/20 @endif">
                                                {{ $user->is_approved ? 'Suspend' : 'Approve' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-650 font-semibold select-none">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-xs text-slate-500">No users found matching requirements.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination Footer -->
            <div class="mt-6 flex items-center justify-between border-t border-slate-800 pt-6">
                <div class="text-xs text-slate-500">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </div>
                <div class="flex items-center space-x-2">
                    @if ($users->onFirstPage())
                        <span class="px-3 py-1.5 rounded-xl border border-slate-800 text-[11px] font-bold text-slate-600 cursor-not-allowed">Previous</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 rounded-xl border border-slate-800 hover:bg-slate-900 text-[11px] font-bold text-slate-350 hover:text-white transition-colors">Previous</a>
                    @endif

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 rounded-xl border border-slate-800 hover:bg-slate-900 text-[11px] font-bold text-slate-355 hover:text-white transition-colors">Next</a>
                    @else
                        <span class="px-3 py-1.5 rounded-xl border border-slate-800 text-[11px] font-bold text-slate-600 cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar forms column -->
        <div class="flex flex-col space-y-6">
            
            <!-- Create User Account -->
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                <h3 class="text-base font-bold text-slate-100 mb-1">Create User Account</h3>
                <p class="text-xs text-slate-400 mb-4">Register new delegated accounts</p>

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
                            @if($caller->isAdmin())
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="admin">Admin</option>
                            @else
                                @if($canManageTeachers)
                                    <option value="teacher">Teacher</option>
                                @endif
                                @if($canManageStudents)
                                    <option value="student">Student</option>
                                @endif
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assign Class (Optional)</label>
                        <select name="school_class_id" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-250 focus:outline-none focus:border-brand-500 cursor-pointer">
                            <option value="">No Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                        Register User
                    </button>
                </form>
            </div>

            <!-- Bulk Register Students -->
            @if($canManageStudents)
            <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
                <h3 class="text-base font-bold text-slate-100 mb-1">Bulk Register Students</h3>
                <p class="text-xs text-slate-400 mb-4">Paste Name and Email (comma-separated, one per line)</p>

                <form action="{{ route('admin.users.bulk') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">CSV/Text Data</label>
                        <textarea name="bulk_data" rows="5" required placeholder="Alice Smith, alice@example.com&#10;Bob Jones, bob@example.com" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700"></textarea>
                    </div>
                    <span class="text-[9px] text-slate-500 block">Note: Users are created as approved students with the default password <strong>'password'</strong>.</span>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-650 hover:bg-indigo-600 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-indigo-600/10">
                        Bulk Register
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
