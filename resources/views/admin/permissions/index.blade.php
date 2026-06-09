@extends('layouts.app')

@section('page_title', 'Role Permissions Grid')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h2 class="text-xl font-bold text-slate-100 font-Outfit">Dynamic Role Permissions Matrix</h2>
        <p class="text-xs text-slate-400 mt-1">Configure capability access rights for Student and Teacher roles. Changes take effect immediately.</p>
    </div>

    <!-- Alert Note -->
    <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl flex items-start space-x-3 max-w-4xl text-xs leading-relaxed">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <span class="font-bold">Security Note:</span> Administrators always bypass permission checks and maintain absolute authority across all features. Students registered publicly default to non-approved state and cannot log in until approved, regardless of these settings.
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 p-4 bg-emerald-500 text-slate-950 font-bold rounded-2xl text-xs shadow-xl hidden flex items-center space-x-2 transition-all duration-300">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="toast-message">Permission Updated!</span>
    </div>

    <!-- Matrix Table -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg max-w-4xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-350">
                <thead>
                    <tr class="bg-slate-900/60 text-xs text-slate-400 uppercase font-semibold">
                        <th class="px-6 py-4 rounded-l-2xl">Permission Scope</th>
                        @foreach($roles as $role)
                            <th class="px-6 py-4 text-center @if($loop->last) rounded-r-2xl @endif">{{ ucfirst($role) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    @foreach($permissions as $permKey => $permDesc)
                        <tr class="hover:bg-slate-900/20 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-200">
                                <div>{{ ucwords(str_replace('_', ' ', $permKey)) }}</div>
                                <div class="text-[10px] text-slate-500 font-normal mt-0.5">{{ $permDesc }}</div>
                            </td>
                            @foreach($roles as $role)
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $isChecked = isset($permissionsMap[$role][$permKey]) && $permissionsMap[$role][$permKey];
                                    @endphp
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               data-role="{{ $role }}" 
                                               data-permission="{{ $permKey }}"
                                               class="sr-only peer perm-toggle"
                                               {{ $isChecked ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-slate-450 after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Permissions Overrides Section -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg max-w-4xl mt-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-100 font-Outfit">User Permission Overrides</h3>
                <p class="text-xs text-slate-400 mt-1">Select an individual teacher or student to configure custom permission overrides that bypass role defaults.</p>
            </div>
            <div>
                <input type="text" id="user-search" placeholder="Search users..." class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700 w-full md:w-48">
            </div>
        </div>

        <div class="overflow-x-auto max-h-[300px] overflow-y-auto pr-1">
            <table class="w-full text-left text-sm text-slate-350">
                <thead class="bg-slate-900 text-xs text-slate-400 uppercase font-semibold sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 rounded-l-2xl">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3 rounded-r-2xl text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850" id="users-table-body">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-900/20 transition-colors user-row">
                            <td class="px-6 py-4 font-semibold text-slate-200 user-name">{{ $u->name }}</td>
                            <td class="px-6 py-4 text-xs user-email">{{ $u->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    @if($u->isTeacher()) bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 @endif">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.permissions.user', $u->id) }}" class="px-3 py-1 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10 inline-block">
                                    Customize Overrides
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-xs text-slate-500">No teachers or students registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Vanilla AJAX Checkbox handler -->
<script>
    document.querySelectorAll('.perm-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const role = this.getAttribute('data-role');
            const permission = this.getAttribute('data-permission');
            const isAllowed = this.checked ? 1 : 0;

            fetch("{{ route('admin.permissions.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    role: role,
                    permission: permission,
                    is_allowed: isAllowed
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast(`Successfully updated permission for ${role}!`);
                } else {
                    showToast("Error updating permission.", true);
                    this.checked = !this.checked; // revert
                }
            })
            .catch(err => {
                showToast("Server connection error.", true);
                this.checked = !this.checked; // revert
            });
        });
    });

    function showToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        const message = document.getElementById('toast-message');
        
        toast.className = isError 
            ? "fixed bottom-5 right-5 z-50 p-4 bg-rose-600 text-white font-bold rounded-2xl text-xs shadow-xl flex items-center space-x-2"
            : "fixed bottom-5 right-5 z-50 p-4 bg-emerald-500 text-slate-950 font-bold rounded-2xl text-xs shadow-xl flex items-center space-x-2";

        message.textContent = msg;
        toast.classList.remove('hidden');

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // User search filtering
    const searchInput = document.getElementById('user-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.user-row').forEach(row => {
                const name = row.querySelector('.user-name').textContent.toLowerCase();
                const email = row.querySelector('.user-email').textContent.toLowerCase();
                if (name.includes(query) || email.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
</script>
@endsection
