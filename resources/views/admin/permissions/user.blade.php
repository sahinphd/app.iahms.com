@extends('layouts.app')

@section('page_title', 'User Permission Overrides')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-100 font-Outfit">Individual Permission Overrides</h2>
            <p class="text-xs text-slate-400 mt-1">Configure user-specific access overrides for <strong>{{ $user->name }}</strong>. Overrides bypass default role permissions.</p>
        </div>
        <div>
            <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-850 text-slate-350 border border-slate-800 hover:border-slate-700 rounded-xl text-xs font-bold transition-all duration-200 inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Matrix</span>
            </a>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg max-w-4xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-400 font-Outfit text-lg font-bold flex items-center justify-center border border-brand-500/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-100">{{ $user->name }}</h3>
                <p class="text-xs text-slate-450 mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 text-xs font-semibold">
            <div class="px-4 py-2 bg-slate-900/60 border border-slate-850 rounded-2xl">
                <span class="text-slate-500">Base Role:</span>
                <span class="text-amber-400 uppercase tracking-wider ml-1">{{ $user->role }}</span>
            </div>
            <div class="px-4 py-2 bg-slate-900/60 border border-slate-850 rounded-2xl">
                <span class="text-slate-500">Status:</span>
                <span class="{{ $user->is_approved ? 'text-emerald-400' : 'text-amber-500' }} ml-1">
                    {{ $user->is_approved ? 'Approved User' : 'Approval Pending' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 p-4 bg-emerald-500 text-slate-950 font-bold rounded-2xl text-xs shadow-xl hidden flex items-center space-x-2 transition-all duration-300">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="toast-message">Override Saved!</span>
    </div>

    <!-- Overrides Matrix Table -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg max-w-4xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-350">
                <thead>
                    <tr class="bg-slate-900/60 text-xs text-slate-400 uppercase font-semibold">
                        <th class="px-6 py-4 rounded-l-2xl">Permission Scope</th>
                        <th class="px-6 py-4 text-center">Inherited Value</th>
                        <th class="px-6 py-4 text-center">Custom Setting</th>
                        <th class="px-6 py-4 rounded-r-2xl text-center">Effective State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    @foreach($permissions as $permKey => $permDesc)
                        @php
                            // Role inherited status
                            $roleAllows = isset($rolePermissions[$permKey]) && $rolePermissions[$permKey]->is_allowed;
                            
                            // User explicit override
                            $overrideRecord = $userOverrides->get($permKey);
                            $overrideValue = 'inherit';
                            if ($overrideRecord !== null) {
                                $overrideValue = $overrideRecord->is_allowed ? 'allow' : 'deny';
                            }

                            // Effective allowance
                            $effectiveAllowed = $overrideRecord !== null ? $overrideRecord->is_allowed : $roleAllows;
                        @endphp
                        <tr class="hover:bg-slate-900/20 transition-colors py-4">
                            <!-- Scope & Description -->
                            <td class="px-6 py-4 font-semibold text-slate-200 max-w-xs">
                                <div>{{ ucwords(str_replace('_', ' ', $permKey)) }}</div>
                                <div class="text-[10px] text-slate-500 font-normal mt-0.5">{{ $permDesc }}</div>
                            </td>

                            <!-- Inherited Role Default -->
                            <td class="px-6 py-4 text-center">
                                @if($roleAllows)
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        ALLOWED (Role Default)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        DENIED (Role Default)
                                    </span>
                                @endif
                            </td>

                            <!-- Override Switcher Selector -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <div class="inline-flex bg-slate-900 border border-slate-800 p-0.5 rounded-xl space-x-0.5">
                                        <!-- Inherit Button -->
                                        <button type="button" 
                                                class="override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 
                                                {{ $overrideValue === 'inherit' ? 'bg-slate-800 text-slate-200 border border-slate-700/50' : 'text-slate-500 hover:text-slate-300' }}"
                                                data-permission="{{ $permKey }}"
                                                data-value="inherit">
                                            Inherit
                                        </button>
                                        <!-- Force Allow -->
                                        <button type="button" 
                                                class="override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 
                                                {{ $overrideValue === 'allow' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-slate-500 hover:text-slate-300' }}"
                                                data-permission="{{ $permKey }}"
                                                data-value="allow">
                                            Allow
                                        </button>
                                        <!-- Force Deny -->
                                        <button type="button" 
                                                class="override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 
                                                {{ $overrideValue === 'deny' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'text-slate-500 hover:text-slate-300' }}"
                                                data-permission="{{ $permKey }}"
                                                data-value="deny">
                                            Deny
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <!-- Effective Status Indicator -->
                            <td class="px-6 py-4 text-center">
                                <div id="status-container-{{ $permKey }}" 
                                     data-role-default="{{ $roleAllows ? '1' : '0' }}"
                                     class="flex justify-center items-center">
                                    @if($effectiveAllowed)
                                        <div class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-400 font-bold text-[10px] flex items-center space-x-1.5 border border-emerald-500/20">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Allowed</span>
                                        </div>
                                    @else
                                        <div class="px-2.5 py-1 rounded-xl bg-rose-500/10 text-rose-400 font-bold text-[10px] flex items-center space-x-1.5 border border-rose-500/20">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Blocked</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- AJAX Switcher Logic -->
<script>
    document.querySelectorAll('.override-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const permission = this.getAttribute('data-permission');
            const value = this.getAttribute('data-value');
            const self = this;

            // Trigger AJAX update
            fetch("{{ route('admin.permissions.user.toggle', $user->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    permission: permission,
                    value: value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`Updated override setting for ${permission}!`);
                    
                    // 1. Update Button visual states in the container
                    const btnContainer = self.parentElement;
                    btnContainer.querySelectorAll('.override-btn').forEach(b => {
                        b.className = "override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-300";
                    });

                    if (value === 'inherit') {
                        self.className = "override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 bg-slate-800 text-slate-200 border border-slate-700/50";
                    } else if (value === 'allow') {
                        self.className = "override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30";
                    } else if (value === 'deny') {
                        self.className = "override-btn px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all duration-200 bg-rose-500/15 text-rose-400 border border-rose-500/30";
                    }

                    // 2. Update Effective Status Indicator
                    updateEffectiveIndicator(permission, value);
                } else {
                    showToast("Failed to update override setting.", true);
                }
            })
            .catch(err => {
                showToast("Server connection error.", true);
            });
        });
    });

    function updateEffectiveIndicator(permission, value) {
        const container = document.getElementById(`status-container-${permission}`);
        const roleDefault = container.getAttribute('data-role-default') === '1';
        
        let isAllowed = false;
        if (value === 'allow') {
            isAllowed = true;
        } else if (value === 'deny') {
            isAllowed = false;
        } else {
            // Inherit
            isAllowed = roleDefault;
        }

        if (isAllowed) {
            container.innerHTML = `
                <div class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-400 font-bold text-[10px] flex items-center space-x-1.5 border border-emerald-500/20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Allowed</span>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="px-2.5 py-1 rounded-xl bg-rose-500/10 text-rose-400 font-bold text-[10px] flex items-center space-x-1.5 border border-rose-500/20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Blocked</span>
                </div>
            `;
        }
    }

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
</script>
@endsection
