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

<div x-data="{ exploreOpen: false, userOpen: false, langOpen: false, mobileMenuOpen: false, mobileUserOpen: false }">
    @php
        $isHome = request()->routeIs('web.home');
        $isCampaigns = request()->routeIs('web.campaigns') || request()->routeIs('web.campaign');
        $isContributions = request()->routeIs('web.contributions') || request()->routeIs('web.contribution');
        $isExpenses = request()->routeIs('web.expenses');
        $isImpact = request()->routeIs('web.impact');
        $isTrust = request()->routeIs('web.trust');
        $isDonors = request()->routeIs('web.donors') || request()->routeIs('web.donor-profile');
        $isRecurring = request()->routeIs('web.recurring');
        $isNotifications = request()->routeIs('web.notifications');
        $isProfile = request()->routeIs('web.profile');
        $isDonorProfile = request()->routeIs('web.donor-profile');
        $isChat = request()->routeIs('web.chat');
    @endphp

    <nav class="lg:sticky lg:top-0 z-50 border-b border-gray-200/70 dark:border-gray-800/70 bg-white/90 dark:bg-gray-900/80 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between gap-4">
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-2">
                    <x-avatar image="{{ getSettingImage('iconImage', 'icon') }}" class="w-10 h-10 ring-1 ring-indigo-100/70 dark:ring-indigo-500/20" />
                    <span class="text-base sm:text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent capitalize">
                        {{ setting('app.name', 'Deenify') }}
                    </span>
                </a>

                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('web.campaigns') }}" wire:navigate class="px-3 py-1.5 text-sm font-semibold rounded-full transition {{ $isCampaigns ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10' }}">
                        {{ __('Campaigns') }}
                    </a>
                    <a href="{{ route('web.contributions') }}" wire:navigate class="px-3 py-1.5 text-sm font-semibold rounded-full transition {{ $isContributions ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10' }}">
                        {{ __('Contributions') }}
                    </a>

                    <div class="relative" @click.away="exploreOpen = false">
                        <button @click="exploreOpen = !exploreOpen" class="px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 rounded-full hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/70 dark:hover:bg-indigo-500/10 transition flex items-center gap-2">
                            {{ __('Explore') }}
                            <x-icon name="o-chevron-down" class="w-4 h-4" />
                        </button>
                        <div x-show="exploreOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 rounded-2xl shadow-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 py-2 z-50">
                            <a href="{{ route('web.expenses') }}" wire:navigate @click="exploreOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isExpenses ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                <x-icon name="o-receipt-percent" class="w-4 h-4" />
                                {{ __('Expenses') }}
                            </a>
                            <a href="{{ route('web.impact') }}" wire:navigate @click="exploreOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isImpact ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                <x-icon name="o-chart-bar" class="w-4 h-4" />
                                {{ __('Impact') }}
                            </a>
                            <a href="{{ route('web.trust') }}" wire:navigate @click="exploreOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isTrust ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                <x-icon name="o-shield-check" class="w-4 h-4" />
                                {{ __('Trust') }}
                            </a>
                            <a href="{{ route('web.donors') }}" wire:navigate @click="exploreOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isDonors ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                <x-icon name="o-users" class="w-4 h-4" />
                                {{ __('Donors') }}
                            </a>
                            <a href="{{ route('web.recurring') }}" wire:navigate @click="exploreOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isRecurring ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                <x-icon name="o-arrow-path" class="w-4 h-4" />
                                {{ __('Give Monthly') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" x-cloak />

                    @auth
                        <a wire:navigate href="{{ route('web.notifications') }}" class="btn btn-ghost btn-circle btn-sm {{ $isNotifications ? 'bg-indigo-50/70 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300' : '' }}">
                            <div class="indicator">
                                <x-icon name="o-bell" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                                @if($this->unreadNotificationsCount > 0)
                                    <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadNotificationsCount }}</span>
                                @endif
                            </div>
                        </a>
                        <a wire:navigate href="{{ route('web.chat') }}" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex {{ $isChat ? 'bg-indigo-50/70 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300' : '' }}">
                            <div class="indicator">
                                <x-icon name="o-chat-bubble-left-right" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                                @if($this->unreadMessagesCount > 0)
                                    <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadMessagesCount }}</span>
                                @endif
                            </div>
                        </a>
                    @endauth

                    <div class="relative" @click.away="langOpen = false">
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

                    @auth
                        <div class="hidden lg:block relative" @click.away="userOpen = false">
                            <button @click="userOpen = !userOpen" class="btn btn-ghost btn-circle btn-sm" aria-label="User menu">
                                <x-avatar :image="userImage(auth()->user(), 'profile', 'thumb')" alt="{{ auth()->user()->name }}" class="w-8 h-8 ring-2 ring-primary/20" />
                            </button>
                            <div x-show="userOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-60 rounded-2xl shadow-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 py-2 z-50">
                                <a wire:navigate href="{{ route('web.profile') }}" @click="userOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isProfile ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                    <x-icon name="o-user" class="w-4 h-4" />
                                    {{ __('My Profile') }}
                                </a>
                                <a wire:navigate href="{{ route('web.donor-profile', auth()->id()) }}" @click="userOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isDonorProfile ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                    <x-icon name="o-identification" class="w-4 h-4" />
                                    {{ __('My Donor Profile') }}
                                </a>
                                @can('dashboard.view')
                                    <a wire:navigate href="{{ route('app.dashboard') }}" @click="userOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                        <x-icon name="o-squares-2x2" class="w-4 h-4" />
                                        {{ __('Dashboard') }}
                                    </a>
                                @endcan
                                <form method="POST" action="{{ route('logout') }}" class="px-4 py-2">
                                    @csrf
                                    <button type="submit" class="w-full btn btn-outline border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white" aria-label="Logout">
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    @guest
                        @if (Route::has('login'))
                            <div class="hidden lg:flex items-center gap-2">
                                <a wire:navigate href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                                    {{ __('Log in') }}
                                </a>
                                @if (Route::has('register'))
                                    <a wire:navigate href="{{ route('register') }}" class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                        {{ __('Register') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endguest

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden btn btn-ghost btn-circle btn-sm" :aria-expanded="mobileMenuOpen.toString()" aria-label="Toggle menu">
                        <x-icon x-show="!mobileMenuOpen" name="o-bars-3" class="w-6 h-6" />
                        <x-icon x-show="mobileMenuOpen" name="o-x-mark" class="w-6 h-6" x-cloak />
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{-- Mobile menu panel --}}
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-gray-950/95 backdrop-blur">
        <div class="px-4 py-4 space-y-2">
            <a href="{{ route('web.campaigns') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isCampaigns ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Campaigns') }}
            </a>
            <a href="{{ route('web.contributions') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isContributions ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Contributions') }}
            </a>
            <a href="{{ route('web.expenses') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isExpenses ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Expenses') }}
            </a>
            <a href="{{ route('web.impact') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isImpact ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Impact') }}
            </a>
            <a href="{{ route('web.trust') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isTrust ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Trust') }}
            </a>
            <a href="{{ route('web.donors') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isDonors ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Donors') }}
            </a>
            <a href="{{ route('web.recurring') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-base font-semibold {{ $isRecurring ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-900/20' : 'text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ __('Give Monthly') }}
            </a>
        </div>
    </div>

    {{-- Mobile Bottom Tab Bar --}}
    <div class="lg:hidden fixed inset-x-0 bottom-0 z-50 bg-white/95 dark:bg-gray-900/95 border-t border-gray-200/80 dark:border-gray-800/80 backdrop-blur shadow-[0_-12px_30px_-24px_rgba(15,23,42,0.5)]">
        <div class="mx-3 my-2 rounded-3xl bg-white/90 dark:bg-gray-950/90 border border-gray-200/70 dark:border-gray-800/70 px-2 py-2">
            <div class="grid grid-cols-5">
            <a href="{{ route('web.home') }}" wire:navigate class="text-xs flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold transition {{ $isHome ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                <x-icon name="o-home" class="w-5 h-5" />
                {{ __('Home') }}
            </a>
            <a href="{{ route('web.campaigns') }}" wire:navigate class="text-xs flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold transition {{ $isCampaigns ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                <x-icon name="o-heart" class="w-5 h-5" />
                {{ __('Campaigns') }}
            </a>
            <a href="{{ route('web.contributions') }}" wire:navigate class="text-xs flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold transition {{ $isContributions ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                <x-icon name="o-sparkles" class="w-5 h-5" />
                {{ __('Impact') }}
            </a>
            @auth
                <a href="{{ route('web.chat') }}" wire:navigate class="text-xs flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold transition {{ $isChat ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300' }}">
                    <div class="indicator">
                        <x-icon name="o-chat-bubble-left-right" class="w-5 h-5" />
                        @if($this->unreadMessagesCount > 0)
                            <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadMessagesCount }}</span>
                        @endif
                    </div>
                    {{ __('Chat') }}
                </a>
                <div class="relative" @click.away="mobileUserOpen = false">
                    <button @click="mobileUserOpen = !mobileUserOpen" class="text-xs flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold text-gray-600 dark:text-gray-300">
                        <x-avatar :image="userImage(auth()->user(), 'profile', 'thumb')" alt="{{ auth()->user()->name }}" class="w-7 h-7 ring-2 ring-primary/20" />
                        {{-- {{ __('Me') }} --}}
                    </button>
                    <div x-show="mobileUserOpen"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute right-0 bottom-14 w-56 rounded-2xl shadow-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 py-2 z-50">
                        <a wire:navigate href="{{ route('web.profile') }}" @click="mobileUserOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isProfile ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                            <x-icon name="o-user" class="w-4 h-4" />
                            {{ __('My Profile') }}
                        </a>
                        <a wire:navigate href="{{ route('web.donor-profile', auth()->id()) }}" @click="mobileUserOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm {{ $isDonorProfile ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                            <x-icon name="o-identification" class="w-4 h-4" />
                            {{ __('My Donor Profile') }}
                        </a>
                        @can('dashboard.view')
                            <a wire:navigate href="{{ route('app.dashboard') }}" @click="mobileUserOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                <x-icon name="o-squares-2x2" class="w-4 h-4" />
                                {{ __('Dashboard') }}
                            </a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}" class="px-4 py-2">
                            @csrf
                            <button type="submit" class="w-full btn btn-outline border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white" aria-label="Logout">
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold text-gray-600 dark:text-gray-300">
                    <x-icon name="o-arrow-right-on-rectangle" class="w-5 h-5" />
                    {{ __('Login') }}
                </a>
                <a href="{{ route('register') }}" wire:navigate class="flex flex-col items-center gap-1 rounded-2xl px-2 py-1 text-[8px] font-semibold text-gray-600 dark:text-gray-300">
                    <x-icon name="o-user-plus" class="w-5 h-5" />
                    {{ __('Join') }}
                </a>
            @endauth
            </div>
        </div>
    </div>

    <div class="lg:hidden h-4"></div>
</div>
