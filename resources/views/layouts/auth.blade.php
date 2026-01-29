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
        <footer class="mt-8">
            <div class="mx-auto w-full max-w-6xl px-6 pb-8">
                <div class="relative overflow-hidden rounded-3xl border border-white/70 bg-gradient-to-br from-white/80 via-white/60 to-white/80 p-5 shadow-[0_12px_28px_-22px_rgba(15,23,42,0.6)] backdrop-blur dark:border-white/10 dark:from-slate-900/70 dark:via-slate-900/50 dark:to-slate-900/70">
                    <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-indigo-200/40 blur-2xl dark:bg-indigo-500/20"></div>
                    <div class="pointer-events-none absolute -bottom-12 left-12 h-28 w-28 rounded-full bg-purple-200/40 blur-2xl dark:bg-purple-500/20"></div>
                    @php
                        $footerPages = \App\Models\Page::published()->orderBy('order')->get(['title', 'slug']);
                    @endphp
                    <div class="flex flex-col gap-4 text-xs text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                        <a class="group inline-flex items-center gap-2" href="{{ route('web.home') }}" wire:navigate>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/90 ring-1 ring-slate-200/70 shadow-sm transition group-hover:-translate-y-0.5 group-hover:shadow-md dark:bg-slate-900 dark:ring-white/10">
                                <x-avatar image="{{ getSettingImage('iconImage', 'icon') }}" class="h-6 w-6" />
                            </span>
                            <span class="text-sm font-semibold text-slate-800 transition group-hover:text-indigo-700 dark:text-slate-100 dark:group-hover:text-indigo-200">
                                {{ setting('app.name', 'Deenify') }}
                            </span>
                            <span class="text-slate-400 dark:text-slate-500">&copy; {{ date('Y') }}</span>
                        </a>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($footerPages as $page)
                                <a class="group relative rounded-lg border border-slate-200/70 bg-white/80 px-3 py-1 text-[11px] text-slate-600 transition hover:border-indigo-200 hover:text-indigo-700 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-indigo-400/40 dark:hover:text-indigo-200" href="{{ route('web.page', $page->slug) }}" wire:navigate>
                                    <span class="absolute inset-0 -z-10 rounded-lg bg-gradient-to-r from-indigo-50/70 via-transparent to-purple-50/70 opacity-0 transition group-hover:opacity-100 dark:from-indigo-400/10 dark:to-purple-400/10"></span>
                                    {{ $page->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <x-toast />

    </div>
@endsection
