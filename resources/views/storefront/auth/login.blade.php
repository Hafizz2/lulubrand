@extends('storefront.layouts.app')

@section('title', 'Sign In — LULU Fashion')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4 bg-[#F9F6F0]">
    <div class="max-w-md w-full bg-white p-10 sm:p-12 shadow-2xl rounded-sm">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-serif font-bold uppercase tracking-widest text-[#221F1F]">Welcome Back</h2>
            <p class="text-[11px] text-[#A38B7E] uppercase tracking-widest mt-2">Sign in to your luxury account</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-[#F9F6F0] text-[#8C6554] text-[11px] font-bold uppercase tracking-wider border border-[#E6DFD5] text-center">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Phone Number</label>
                <input type="tel" name="phone" placeholder="+251 9XX XXX XXXX" value="{{ old('phone') }}" required autofocus class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                @error('phone')
                    <p class="text-[10px] text-[#C49A9A] mt-2 font-bold uppercase tracking-widest">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                @error('password')
                    <p class="text-[10px] text-[#C49A9A] mt-2 font-bold uppercase tracking-widest">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-[11px] font-semibold tracking-wider text-[#A38B7E]">
                <label class="flex items-center space-x-2 cursor-pointer hover:text-[#8C6554] transition-colors">
                    <input type="checkbox" name="remember" class="rounded border-[#E6DFD5] text-[#8C6554] focus:ring-[#8C6554]">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full py-4 bg-[#221F1F] text-white font-bold text-[11px] uppercase tracking-[0.2em] hover:bg-[#8C6554] transition-colors rounded-full shadow-lg mt-4">
                Sign In
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-[#E6DFD5] text-center flex flex-col space-y-3">
            <span class="text-[10px] text-[#A38B7E] uppercase tracking-widest">New to LULU?</span>
            <a href="{{ route('register') }}" class="text-[11px] font-bold text-[#221F1F] uppercase tracking-[0.2em] hover:text-[#8C6554] transition-colors">Create an Account</a>
        </div>
    </div>
</div>
@endsection
