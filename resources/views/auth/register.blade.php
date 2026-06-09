@extends('layouts.app')

@section('page_title', 'Register')

@section('content')
<div class="min-h-[75vh] flex flex-col items-center justify-center py-6 px-4">
    <div class="max-w-md w-full bg-slate-950/50 backdrop-blur-md border border-slate-800/80 p-8 rounded-3xl shadow-xl shadow-brand-500/5 transition-all duration-300">
        
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-100">Create an Account</h2>
            <p class="text-xs text-slate-400 mt-2">Join our learning platform and get started today</p>
        </div>

        <form class="space-y-4" action="{{ route('register') }}" method="POST">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Full Name</label>
                <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}"
                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-600"
                    placeholder="John Doe">
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Email Address</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-600"
                    placeholder="john@example.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-600"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-600"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full py-3 px-4 mt-2 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-sm font-bold shadow-lg shadow-brand-500/20 focus:outline-none transform hover:-translate-y-0.5 transition-all duration-200">
                Register
            </button>
        </form>

        <div class="text-center mt-4 text-xs text-slate-500">
            Already have an account? 
            <a href="{{ route('login') }}" class="font-semibold text-brand-400 hover:text-brand-300 transition-colors">Log in here</a>
        </div>
    </div>
</div>
@endsection
