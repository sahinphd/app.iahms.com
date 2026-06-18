@extends('layouts.app')

@section('page_title', 'Theme Settings')

@section('content')
<div class="space-y-6 max-w-4xl">

    <!-- Header -->
    <div>
        <h2 class="text-xl font-bold text-slate-100 font-Outfit">Theme & Branding Customizer</h2>
        <p class="text-xs text-slate-400 mt-1">Configure layout canvas colors and brand naming text site-wide. Changes are active instantly upon saving.</p>
    </div>

    <!-- Presets Section -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
        <h3 class="text-sm font-bold text-slate-200 mb-3 font-Outfit">Quick Theme Presets</h3>
        <p class="text-xs text-slate-400 mb-4">Click a preset to automatically load harmonious color configurations below.</p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Preset Violet -->
            <button type="button" 
                    onclick="applyPreset('#8b5cf6', '#0f172a', '#020617')"
                    class="p-4 bg-slate-900 border border-slate-800 hover:border-brand-500 rounded-2xl text-left group transition-all duration-200">
                <div class="flex items-center space-x-2">
                    <span class="w-3.5 h-3.5 rounded-full bg-violet-500 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-900 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-950 block border border-white/10"></span>
                </div>
                <div class="text-xs font-bold text-slate-300 mt-3 group-hover:text-slate-100">Default Violet</div>
            </button>

            <!-- Preset Ocean Blue -->
            <button type="button" 
                    onclick="applyPreset('#0284c7', '#0f172a', '#030712')"
                    class="p-4 bg-slate-900 border border-slate-800 hover:border-brand-500 rounded-2xl text-left group transition-all duration-200">
                <div class="flex items-center space-x-2">
                    <span class="w-3.5 h-3.5 rounded-full bg-sky-500 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-900 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-950 block border border-white/10"></span>
                </div>
                <div class="text-xs font-bold text-slate-300 mt-3 group-hover:text-slate-100">Ocean Blue</div>
            </button>

            <!-- Preset Emerald Forest -->
            <button type="button" 
                    onclick="applyPreset('#059669', '#06120e', '#020604')"
                    class="p-4 bg-slate-900 border border-slate-800 hover:border-brand-500 rounded-2xl text-left group transition-all duration-200">
                <div class="flex items-center space-x-2">
                    <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-900 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-950 block border border-white/10"></span>
                </div>
                <div class="text-xs font-bold text-slate-300 mt-3 group-hover:text-slate-100">Emerald Forest</div>
            </button>

            <!-- Preset Crimson Dark -->
            <button type="button" 
                    onclick="applyPreset('#e11d48', '#1c0d12', '#0c0407')"
                    class="p-4 bg-slate-900 border border-slate-800 hover:border-brand-500 rounded-2xl text-left group transition-all duration-200">
                <div class="flex items-center space-x-2">
                    <span class="w-3.5 h-3.5 rounded-full bg-rose-500 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-900 block border border-white/10"></span>
                    <span class="w-3.5 h-3.5 rounded-full bg-slate-950 block border border-white/10"></span>
                </div>
                <div class="text-xs font-bold text-slate-300 mt-3 group-hover:text-slate-100">Crimson Dark</div>
            </button>
        </div>
    </div>

    <!-- Main Customizer Form -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
        <form action="{{ route('admin.theme.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Branding Section -->
            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-850 pb-2">Branding Text</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Site Logo Text</label>
                        <input type="text" 
                               name="site_logo_text" 
                               id="site_logo_text"
                               value="{{ $logoText }}" 
                               required 
                               placeholder="e.g. IAHMS LMS"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Logo Subtext / Subtitle</label>
                        <input type="text" 
                               name="site_logo_subtext" 
                               id="site_logo_subtext"
                               value="{{ $logoSubtext }}" 
                               placeholder="e.g. SECURE LEARNING"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                    </div>
                </div>
            </div>

            <!-- Colors Section -->
            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-850 pb-2">Theme Colors</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <!-- Accent color -->
                    <div class="p-4 bg-slate-900/40 border border-slate-850 rounded-2xl flex flex-col justify-between h-28">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Primary Accent</label>
                            <span class="text-[9px] text-slate-500 block mt-0.5">Applies to buttons, links & sidebar highlight</span>
                        </div>
                        <div class="flex items-center space-x-3 mt-3">
                            <input type="color" 
                                   name="theme_primary_color" 
                                   id="theme_primary_color"
                                   value="{{ $primaryColor }}" 
                                   class="w-8 h-8 rounded-lg cursor-pointer bg-transparent border-0">
                            <span id="label_primary_color" class="text-xs font-mono text-slate-350">{{ $primaryColor }}</span>
                        </div>
                    </div>

                    <!-- Canvas Background color -->
                    <div class="p-4 bg-slate-900/40 border border-slate-850 rounded-2xl flex flex-col justify-between h-28">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Body Background</label>
                            <span class="text-[9px] text-slate-500 block mt-0.5">Main application canvas backdrop color</span>
                        </div>
                        <div class="flex items-center space-x-3 mt-3">
                            <input type="color" 
                                   name="theme_bg_color" 
                                   id="theme_bg_color"
                                   value="{{ $bgColor }}" 
                                   class="w-8 h-8 rounded-lg cursor-pointer bg-transparent border-0">
                            <span id="label_bg_color" class="text-xs font-mono text-slate-350">{{ $bgColor }}</span>
                        </div>
                    </div>

                    <!-- Sidebar Background color -->
                    <div class="p-4 bg-slate-900/40 border border-slate-850 rounded-2xl flex flex-col justify-between h-28">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Sidebar Backing</label>
                            <span class="text-[9px] text-slate-500 block mt-0.5">Vertical navigation sidebar container background</span>
                        </div>
                        <div class="flex items-center space-x-3 mt-3">
                            <input type="color" 
                                   name="theme_sidebar_color" 
                                   id="theme_sidebar_color"
                                   value="{{ $sidebarColor }}" 
                                   class="w-8 h-8 rounded-lg cursor-pointer bg-transparent border-0">
                            <span id="label_sidebar_color" class="text-xs font-mono text-slate-350">{{ $sidebarColor }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UPI QR Payment Section -->
            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-850 pb-2">UPI QR & Payment Settings</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 p-4 bg-slate-900/40 border border-slate-850 rounded-2xl flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">UPI QR Code Image</label>
                            <span class="text-[9px] text-slate-500 block">Upload a QR Code image (PNG/JPG) for student payment display</span>
                        </div>
                        <div class="mt-3 space-y-3">
                            @if($paymentQrCode)
                                <div class="relative w-24 h-24 rounded-lg overflow-hidden border border-slate-800 bg-white p-1">
                                    <img src="{{ asset($paymentQrCode) }}" class="w-full h-full object-contain" alt="Current QR Code">
                                </div>
                            @endif
                            <input type="file" 
                                   name="payment_qr_code" 
                                   id="payment_qr_code"
                                   accept="image/*"
                                   class="block w-full text-[10px] text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-xl file:border-0 file:text-[10px] file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 cursor-pointer">
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Payee / Merchant Name</label>
                                <input type="text" 
                                       name="payment_upi_name" 
                                       id="payment_upi_name"
                                       value="{{ $paymentUpiName }}" 
                                       placeholder="e.g. IAHMS LMS"
                                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">UPI ID (VPA)</label>
                                <input type="text" 
                                       name="payment_upi_id" 
                                       id="payment_upi_id"
                                       value="{{ $paymentUpiId }}" 
                                       placeholder="e.g. pay@upi"
                                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Payment Instructions</label>
                            <textarea name="payment_instructions" 
                                      id="payment_instructions"
                                      rows="3"
                                      placeholder="Provide details about the admission/registration fees and approval timeline..."
                                      class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">{{ $paymentInstructions }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Analytics Section -->
            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-850 pb-2">Google Analytics Integration</h4>
                <div class="p-6 bg-slate-900/40 border border-slate-850 rounded-3xl space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Google Analytics Measurement ID (GTID)</label>
                        <input type="text" 
                               name="google_analytics_id" 
                               id="google_analytics_id"
                               value="{{ $googleAnalyticsId }}" 
                               placeholder="e.g. G-XXXXXXXXXX"
                               class="max-w-md w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        <span class="text-[10px] text-slate-500 block mt-1.5">Enter your Google Analytics Measurement ID (starting with "G-"). When filled, the tracking script tag is automatically injected globally in all layouts.</span>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4 border-t border-slate-850">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                    Save Branding Configurations
                </button>
            </div>

        </form>
    </div>

</div>

<!-- Color Presets Application Javascript -->
<script>
    // Link Hex label to Color Picker changes
    function setupColorSync(inputId, labelId) {
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);
        if (input && label) {
            input.addEventListener('input', function() {
                label.textContent = this.value.toUpperCase();
            });
        }
    }

    setupColorSync('theme_primary_color', 'label_primary_color');
    setupColorSync('theme_bg_color', 'label_bg_color');
    setupColorSync('theme_sidebar_color', 'label_sidebar_color');

    function applyPreset(primary, bg, sidebar) {
        // Set color values
        document.getElementById('theme_primary_color').value = primary;
        document.getElementById('theme_bg_color').value = bg;
        document.getElementById('theme_sidebar_color').value = sidebar;

        // Set hex label texts
        document.getElementById('label_primary_color').textContent = primary.toUpperCase();
        document.getElementById('label_bg_color').textContent = bg.toUpperCase();
        document.getElementById('label_sidebar_color').textContent = sidebar.toUpperCase();

        // Temporary visual effect to denote preset apply success
        const formFields = document.querySelectorAll('input[type="color"]');
        formFields.forEach(el => {
            el.classList.add('scale-110');
            setTimeout(() => el.classList.remove('scale-110'), 200);
        });
    }
</script>
@endsection
