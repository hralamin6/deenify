@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">
        {{-- Header --}}
    <livewire:web::header/>

        {{-- Content --}}
        @yield('content')

        @isset($slot)
            {{ $slot }}
        @endisset

          {{-- Footer --}}
    <footer class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 lg:flex-row">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-lg">
                    <x-icon name="o-heart" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">Deenify</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Donation Management System') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ __('Privacy') }}</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ __('Terms') }}</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ __('Support') }}</a>
            </div>
        </div>
    </footer>
    </div>
@endsection
