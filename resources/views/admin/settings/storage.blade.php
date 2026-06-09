@extends('layouts.app')

@section('page_title', 'Storage Setup')

@section('content')
<div class="space-y-6 max-w-4xl">

    <!-- Header -->
    <div>
        <h2 class="text-xl font-bold text-slate-100 font-Outfit">Multi-Provider Storage Setup</h2>
        <p class="text-xs text-slate-400 mt-1">Configure and manage video streaming and document attachments. Swap between Local, Google Cloud, Cloudflare, and Mux providers dynamically.</p>
    </div>

    <!-- Main Configuration Panel -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-6 shadow-lg">
        <form action="{{ route('admin.settings.storage.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Driver Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 font-Outfit">Active Storage Driver</label>
                <select id="active_storage_driver" name="active_storage_driver" onchange="toggleDriverForms(this.value)" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                    <option value="local" {{ $activeDriver === 'local' ? 'selected' : '' }}>Local Storage (Default Mock Mode)</option>
                    <option value="gcp" {{ $activeDriver === 'gcp' ? 'selected' : '' }}>Google Cloud Storage (Signed URLs)</option>
                    <option value="cloudflare" {{ $activeDriver === 'cloudflare' ? 'selected' : '' }}>Cloudflare R2 (S3-Compatible)</option>
                    <option value="mux" {{ $activeDriver === 'mux' ? 'selected' : '' }}>Mux Video (Direct Streaming API)</option>
                </select>
                <p class="text-[10px] text-slate-500 mt-1.5">Note: Switching drivers will apply to all subsequent uploads immediately. Existing files on other providers will resolve based on the driver configurations below.</p>
            </div>

            <!-- Provider Specific Configurations -->
            <div class="pt-4 border-t border-slate-850">
                
                <!-- 1. LOCAL STORAGE SECTION -->
                <div id="section_local" class="driver-section space-y-4 hidden">
                    <div class="p-4 bg-slate-900/20 border border-slate-850 rounded-2xl">
                        <h4 class="text-xs font-bold text-slate-200 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Local Disk Storage Driver Active</span>
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                            No credentials required. Uploads are stored locally in the <code>storage/app/public</code> folder. 
                            Secure signed streaming is fully emulated using Laravel temporary signed route tokens with 15-minute expirations.
                        </p>
                    </div>
                </div>

                <!-- 2. GOOGLE CLOUD STORAGE SECTION -->
                <div id="section_gcp" class="driver-section space-y-6 hidden">
                    <h4 class="text-xs font-semibold text-slate-300 uppercase tracking-wider border-b border-slate-850 pb-2 font-Outfit">Google Cloud Configuration</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">GCP Project ID</label>
                            <input type="text" name="gcp_project_id" value="{{ $gcpProjectId }}" placeholder="e.g. lms-secure-gcp" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">GCP Bucket Name</label>
                            <input type="text" name="gcp_bucket" value="{{ $gcpBucket }}" placeholder="e.g. lms-assets-bucket" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Google Application Credentials (JSON Key File)</label>
                        <textarea name="gcp_key_file" rows="6" placeholder='{ "type": "service_account", "project_id": ... }' class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700 font-mono">{{ $gcpKeyFile }}</textarea>
                        <span class="text-[9px] text-slate-500 mt-1 block">Paste the raw text content of your Google Service Account key JSON file. This is required to generate v4 Signed URLs dynamically.</span>
                    </div>
                </div>

                <!-- 3. CLOUDFLARE R2 SECTION -->
                <div id="section_cloudflare" class="driver-section space-y-6 hidden">
                    <h4 class="text-xs font-semibold text-slate-300 uppercase tracking-wider border-b border-slate-850 pb-2 font-Outfit">Cloudflare R2 (S3-Compatible) Configuration</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">R2 Endpoint URL</label>
                            <input type="text" name="r2_endpoint" value="{{ $r2Endpoint }}" placeholder="https://<account-id>.r2.cloudflarestorage.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">R2 Bucket Name</label>
                            <input type="text" name="r2_bucket" value="{{ $r2Bucket }}" placeholder="e.g. lms-files" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">R2 Access Key ID</label>
                            <input type="text" name="r2_access_key_id" value="{{ $r2AccessKeyId }}" placeholder="Access Key ID" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">R2 Secret Access Key</label>
                            <input type="password" name="r2_secret_access_key" value="{{ $r2SecretAccessKey }}" placeholder="••••••••••••••••" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">R2 Region (Optional)</label>
                        <input type="text" name="r2_region" value="{{ $r2Region ?: 'auto' }}" placeholder="auto" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                    </div>
                </div>

                <!-- 4. MUX VIDEO SECTION -->
                <div id="section_mux" class="driver-section space-y-6 hidden">
                    <h4 class="text-xs font-semibold text-slate-300 uppercase tracking-wider border-b border-slate-850 pb-2 font-Outfit">Mux Direct Streaming Configuration</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Mux Access Token ID</label>
                            <input type="text" name="mux_token_id" value="{{ $muxTokenId }}" placeholder="Access Token ID" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Mux Secret Access Token</label>
                            <input type="password" name="mux_token_secret" value="{{ $muxTokenSecret }}" placeholder="••••••••••••••••" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Mux Playback Signing Key ID (Optional)</label>
                            <input type="text" name="mux_signing_key_id" value="{{ $muxSigningKeyId }}" placeholder="Signing Key ID (for secure streaming)" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Mux Private Key (Optional - PEM format)</label>
                        <textarea name="mux_private_key" rows="6" placeholder="-----BEGIN RSA PRIVATE KEY-----&#10;...&#10;-----END RSA PRIVATE KEY-----" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-brand-500 placeholder-slate-700 font-mono">{{ $muxPrivateKey }}</textarea>
                        <span class="text-[9px] text-slate-500 mt-1 block">If a Signing Key ID and Private Key are configured, Mux playback links will be signed using secure temporary JWT RS256 hashes. Otherwise, assets will stream publicly.</span>
                    </div>
                </div>

            </div>

            <!-- Submit Section -->
            <div class="flex justify-end pt-4 border-t border-slate-850">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10 uppercase tracking-wider font-Outfit">
                    Save Storage Setup
                </button>
            </div>

        </form>
    </div>

</div>

<!-- UI Toggle Javascript -->
<script>
    function toggleDriverForms(driver) {
        // Hide all sections
        const sections = document.querySelectorAll('.driver-section');
        sections.forEach(s => s.classList.add('hidden'));

        // Show selected section
        const targetSection = document.getElementById(`section_${driver}`);
        if (targetSection) {
            targetSection.classList.remove('hidden');
        }
    }

    // Initialize display on load
    document.addEventListener('DOMContentLoaded', function() {
        const activeDriver = document.getElementById('active_storage_driver').value;
        toggleDriverForms(activeDriver);
    });
</script>
@endsection
