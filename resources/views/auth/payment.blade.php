@extends('layouts.app')

@section('page_title', 'Payment Options')

@section('content')
<div class="min-h-[75vh] flex flex-col items-center justify-center py-6 px-4">
    <div class="max-w-md w-full bg-slate-950/50 backdrop-blur-md border border-slate-800/80 p-8 rounded-3xl shadow-xl shadow-brand-500/5 transition-all duration-300">
        
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-100">Registration Payment</h2>
            <p class="text-xs text-slate-400 mt-2">Complete your admission fee payment to activate your learning account</p>
        </div>

        <div class="space-y-6">
            <!-- QR Code Box -->
            <div class="flex flex-col items-center justify-center p-6 bg-slate-900 border border-slate-850 rounded-2xl">
                @if($paymentQrCode)
                    <div class="relative w-48 h-48 rounded-2xl overflow-hidden border-2 border-slate-800 bg-white p-2.5 shadow-lg">
                        <img src="{{ asset($paymentQrCode) }}" class="w-full h-full object-contain" alt="UPI QR Code">
                    </div>
                @else
                    <!-- Fallback Mock QR Code SVG -->
                    <div class="relative w-48 h-48 rounded-2xl overflow-hidden border-2 border-slate-800 bg-slate-950 flex flex-col items-center justify-center p-4 shadow-lg text-slate-700">
                        <svg class="w-16 h-16 text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm0 12h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zM17 8h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span class="text-[10px] font-semibold text-slate-500 text-center uppercase tracking-wider">No QR Code Uploaded</span>
                    </div>
                @endif
                <span class="text-[9px] text-slate-500 uppercase tracking-widest mt-3 font-semibold">Scan QR using any UPI App</span>
            </div>

            <!-- Merchant & UPI details -->
            <div class="bg-slate-900/40 border border-slate-850 p-4 rounded-2xl space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Payee Name</span>
                    <span class="text-slate-200 font-bold font-Outfit">{{ $paymentUpiName }}</span>
                </div>
                
                <div class="flex justify-between items-center text-xs border-t border-slate-850 pt-3">
                    <div>
                        <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] block">UPI ID</span>
                        <span id="upi-id-text" class="text-slate-200 font-mono text-[11px] font-bold">{{ $paymentUpiId }}</span>
                    </div>
                    <button type="button" 
                            onclick="copyUpiId()"
                            class="px-2.5 py-1 bg-brand-500/10 hover:bg-brand-500 text-brand-400 hover:text-white rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors duration-200">
                        Copy
                    </button>
                </div>
            </div>

            <!-- Instructions -->
            @if($paymentInstructions)
                <div class="p-4 bg-slate-950/80 border border-slate-850/60 rounded-2xl text-[11px] text-slate-400 leading-relaxed space-y-1">
                    <span class="font-bold text-slate-300 block uppercase tracking-wider text-[9px] mb-1.5">Instructions:</span>
                    <p class="whitespace-pre-line">{!! e($paymentInstructions) !!}</p>
                </div>
            @endif

            <!-- Done Action -->
            <div class="pt-2">
                <a href="{{ route('login') }}"
                   class="w-full inline-flex items-center justify-center py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-sm font-bold shadow-lg shadow-brand-500/20 focus:outline-none transform hover:-translate-y-0.5 transition-all duration-200">
                    Done! Go to Log In
                </a>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            Need help? Contact system support or admin.
        </div>
    </div>
</div>

<script>
    function copyUpiId() {
        const text = document.getElementById('upi-id-text').innerText;
        navigator.clipboard.writeText(text).then(() => {
            // Find copy button and show feedback
            const btn = event.target;
            const originalText = btn.innerText;
            btn.innerText = 'Copied!';
            btn.classList.replace('text-brand-400', 'text-emerald-400');
            btn.classList.replace('bg-brand-500/10', 'bg-emerald-500/10');
            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.replace('text-emerald-400', 'text-brand-400');
                btn.classList.replace('bg-emerald-500/10', 'bg-brand-500/10');
            }, 2000);
        });
    }
</script>
@endsection
