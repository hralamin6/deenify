<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public function switchLanguage($locale)
    {
        if (in_array($locale, ['en', 'ar', 'bn'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->redirect(url()->previous(), navigate: true);
        }
    }

    public function getUnreadMessagesCountProperty()
    {
        if (! auth()->check()) {
            return 0;
        }

        return auth()->user()
            ->conversations()
            ->get()
            ->sum(fn ($conversation) => $conversation->getUnreadCount(auth()->id()));
    }

    public function getUnreadNotificationsCountProperty()
    {
        if (! auth()->check()) {
            return 0;
        }

        return auth()->user()->unreadNotifications()->count();
    }

    #[On('message-received')]
    #[On('notification-received')]
    public function refreshCounts()
    {
        $this->dispatch('$refresh');
    }
};
?>

<div>
    @php
        $isHome = request()->routeIs('web.home');
        $isCampaigns = request()->routeIs('web.campaigns') || request()->routeIs('web.campaign');
        $isContributions = request()->routeIs('web.contributions') || request()->routeIs('web.contribution');
        $isNotifications = request()->routeIs('web.notifications');
        $isProfile = request()->routeIs('web.profile') || request()->routeIs('web.donor-profile');
    @endphp

    <nav class="sticky top-0 z-50 border-b border-gray-200/80 dark:border-gray-800/80 bg-white/85 dark:bg-gray-950/80 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between">
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-2">
                    <x-avatar image="{{ getSettingImage('iconImage', 'icon') }}" class="w-10 h-10 ring-1 ring-indigo-100/70 dark:ring-indigo-500/20" />
                    <span class="hidden sm:block text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent capitalize">
                        {{ setting('app.name', 'Deenify') }}
                    </span>
                </a>

                <div class="hidden lg:flex items-center gap-1 rounded-full bg-white/80 dark:bg-gray-900/70 border border-gray-200/70 dark:border-gray-800/70 px-2 py-1">
                    <a href="{{ route('web.campaigns') }}" wire:navigate class="px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 rounded-full hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10 transition">
                        {{ __('Campaigns') }}
                    </a>
                    <a href="{{ route('web.contributions') }}" wire:navigate class="px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 rounded-full hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10 transition">
                        {{ __('Contributions') }}
                    </a>
                    <a href="{{ route('web.recurring') }}" wire:navigate class="px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 rounded-full hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10 transition">
                        {{ __('Give Monthly') }}
                    </a>
                    <a href="{{ route('web.impact') }}" wire:navigate class="px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 rounded-full hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10 transition">
                        {{ __('Impact') }}
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" x-cloak />

                    @auth
                        <a wire:navigate href="{{ route('web.notifications') }}" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex">
                            <div class="indicator">
                                <x-icon name="o-bell" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                                @if($this->unreadNotificationsCount > 0)
                                    <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadNotificationsCount }}</span>
                                @endif
                            </div>
                        </a>
                    @endauth

                    <div class="relative" x-data="{ langOpen: false }" @click.away="langOpen = false">
                        <button @click="langOpen = !langOpen" class="btn btn-ghost btn-circle btn-sm" aria-label="Language">
                            <x-icon name="o-language" class="w-5 h-5" />
                        </button>
                        <div x-show="langOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-52 rounded-2xl shadow-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 py-2 z-50">
                            <a wire:click="switchLanguage('en')" @click="langOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                <span class="fi fi-gb"></span>
                                <span>English</span>
                                @if(app()->getLocale() === 'en')
                                    <x-icon name="o-check" class="w-4 h-4 ml-auto text-indigo-600" />
                                @endif
                            </a>
                            <a wire:click="switchLanguage('ar')" @click="langOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer {{ app()->getLocale() === 'ar' ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                <span class="fi fi-sa"></span>
                                <span>العربية</span>
                                @if(app()->getLocale() === 'ar')
                                    <x-icon name="o-check" class="w-4 h-4 ml-auto text-indigo-600" />
                                @endif
                            </a>
                            <a wire:click="switchLanguage('bn')" @click="langOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer {{ app()->getLocale() === 'bn' ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                <span class="fi fi-bd"></span>
                                <span>বাংলা</span>
                                @if(app()->getLocale() === 'bn')
                                    <x-icon name="o-check" class="w-4 h-4 ml-auto text-indigo-600" />
                                @endif
                            </a>
                        </div>
                    </div>

                    @if (Route::has('login'))
                        <div class="hidden lg:flex items-center gap-2">
                            @auth
                                <a wire:navigate href="{{ route('web.profile') }}" class="btn btn-ghost btn-sm">
                                    {{ __('My Profile') }}
                                </a>
                                @can('dashboard.view')
                                    <a wire:navigate href="{{ route('app.dashboard') }}" class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                        {{ __('Dashboard') }}
                                    </a>
                                @endcan
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-circle btn-error btn-xs hover:scale-105 transition-transform" aria-label="Logout">
                                        <x-icon name="o-power" />
                                    </button>
                                </form>
                            @else
                                <a wire:navigate href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                                    {{ __('Log in') }}
                                </a>
                                @if (Route::has('register'))
                                    <a wire:navigate href="{{ route('register') }}" class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                        {{ __('Register') }}
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{-- Mobile Bottom Tab Bar --}}
    <div class="lg:hidden fixed inset-x-0 bottom-0 z-50 bg-white/95 dark:bg-gray-950/95 border-t border-gray-200/80 dark:border-gray-800/80 backdrop-blur">
        <div class="grid grid-cols-5 px-2 py-2">
            <a href="{{ route('web.home') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold transition {{ $isHome ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                <x-icon name="o-home" class="w-5 h-5" />
                {{ __('Home') }}
            </a>
            <a href="{{ route('web.campaigns') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold transition {{ $isCampaigns ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                <x-icon name="o-heart" class="w-5 h-5" />
                {{ __('Campaigns') }}
            </a>
            <a href="{{ route('web.contributions') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold transition {{ $isContributions ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                <x-icon name="o-sparkles" class="w-5 h-5" />
                {{ __('Impact') }}
            </a>
            @auth
                <a href="{{ route('web.notifications') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold transition {{ $isNotifications ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                    <div class="indicator">
                        <x-icon name="o-bell" class="w-5 h-5" />
                        @if($this->unreadNotificationsCount > 0)
                            <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadNotificationsCount }}</span>
                        @endif
                    </div>
                    {{ __('Alerts') }}
                </a>
                <a href="{{ route('web.profile') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold transition {{ $isProfile ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                    <x-icon name="o-user" class="w-5 h-5" />
                    {{ __('Profile') }}
                </a>
            @else
                <a href="{{ route('login') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold text-gray-600 dark:text-gray-300">
                    <x-icon name="o-arrow-right-on-rectangle" class="w-5 h-5" />
                    {{ __('Login') }}
                </a>
                <a href="{{ route('register') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[11px] font-semibold text-gray-600 dark:text-gray-300">
                    <x-icon name="o-user-plus" class="w-5 h-5" />
                    {{ __('Join') }}
                </a>
            @endauth
        </div>
    </div>

    <div class="lg:hidden h-16"></div>
</div>
