@extends('layouts.app')

@section('page_title', 'Log In')

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center py-6 px-4">
    <div class="max-w-md w-full bg-slate-950/50 backdrop-blur-md border border-slate-800/80 p-8 rounded-3xl shadow-xl shadow-brand-500/5 transition-all duration-300">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-slate-100">Welcome Back</h2>
            <p class="text-xs text-slate-400 mt-2">Sign in to your learning dashboard to continue</p>
        </div>

        <form class="space-y-5" action="{{ route('login') }}" method="POST">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-600"
                    placeholder="you@example.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-600"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 text-slate-400 select-none cursor-pointer">
                    <input id="remember" name="remember" type="checkbox"
                        class="rounded bg-slate-900 border-slate-800 text-brand-600 focus:ring-brand-500/30">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-sm font-bold shadow-lg shadow-brand-500/20 focus:outline-none transform hover:-translate-y-0.5 transition-all duration-200">
                Sign In
            </button>
        </form>

        <div class="text-center mt-6 text-xs text-slate-500">
            Don't have an account? 
            <a href="{{ route('register') }}" class="font-semibold text-brand-400 hover:text-brand-300 transition-colors">Register here</a>
        </div>
    </div>
</div>
@endsection
