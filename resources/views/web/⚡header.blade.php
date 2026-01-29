<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $mobileMenuOpen = false;

    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }

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
        if (!auth()->check()) return 0;
        
        return auth()->user()
            ->conversations()
            ->get()
            ->sum(fn($conversation) => $conversation->getUnreadCount(auth()->id()));
    }

    public function getUnreadNotificationsCountProperty()
    {
        if (!auth()->check()) return 0;
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

<nav class="sticky top-0 z-50 backdrop-blur-sm bg-white/70 dark:bg-gray-900/70 border-b border-gray-200 dark:border-gray-800 shadow-sm" x-data="{ mobileMenuOpen: @entangle('mobileMenuOpen'), langDropdownOpen: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-1">
                    <x-avatar image="{{ getSettingImage('iconImage', 'icon') }}" class="w-12 h-12" />
                    <div class="block">
                        <p class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent capitalize">{{ setting('app.name', 'Deenify') }}</p>
                    </div>
                </a>
            </div>
            
            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('web.campaigns') }}" wire:navigate class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                    {{ __('Campaigns') }}
                </a>
                <a href="{{ route('web.home') }}#features" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                    {{ __('Features') }}
                </a>
                <a href="{{ route('web.home') }}#reports" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                    {{ __('Reports') }}
                </a>
            </div>

                {{-- Right Side Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Theme Toggle --}}
                    <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" x-cloak />

                    @auth
                        {{-- Messages Badge --}}
                        <a wire:navigate href="{{ route('app.chat') }}" class="btn btn-ghost btn-circle btn-sm">
                            <div class="indicator">
                                <x-icon name="o-chat-bubble-left-right" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                                @if($this->unreadMessagesCount > 0)
                                    <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadMessagesCount }}</span>
                                @endif
                            </div>
                        </a>

                        {{-- Notifications Badge --}}
                        <a wire:navigate href="{{ route('app.notifications') }}" class="btn btn-ghost btn-circle btn-sm">
                            <div class="indicator">
                                <x-icon name="o-bell" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                                @if($this->unreadNotificationsCount > 0)
                                    <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadNotificationsCount }}</span>
                                @endif
                            </div>
                        </a>
                    @endauth

                    {{-- Language Dropdown --}}
                <div class="relative" @click.away="langDropdownOpen = false">
                    <button @click="langDropdownOpen = !langDropdownOpen" class="btn btn-ghost btn-circle btn-sm">
                        <x-icon name="o-language" class="w-5 h-5" />
                    </button>
                    <div x-show="langDropdownOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-52 rounded-2xl shadow-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 py-2 z-50">
                        <a wire:click="switchLanguage('en')" @click="langDropdownOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <span class="fi fi-gb"></span>
                            <span>English</span>
                            @if(app()->getLocale() === 'en')
                                <x-icon name="o-check" class="w-4 h-4 ml-auto text-indigo-600" />
                            @endif
                        </a>
                        <a wire:click="switchLanguage('ar')" @click="langDropdownOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer {{ app()->getLocale() === 'ar' ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <span class="fi fi-sa"></span>
                            <span>العربية</span>
                            @if(app()->getLocale() === 'ar')
                                <x-icon name="o-check" class="w-4 h-4 ml-auto text-indigo-600" />
                            @endif
                        </a>
                        <a wire:click="switchLanguage('bn')" @click="langDropdownOpen = false" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer {{ app()->getLocale() === 'bn' ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <span class="fi fi-bd"></span>
                            <span>বাংলা</span>
                            @if(app()->getLocale() === 'bn')
                                <x-icon name="o-check" class="w-4 h-4 ml-auto text-indigo-600" />
                            @endif
                        </a>
                    </div>
                </div>

                {{-- Auth Buttons (Desktop) --}}
                @if (Route::has('login'))
                    <div class="hidden sm:flex items-center gap-2">
                        @auth
                        <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="btn btn-circle btn-error btn-xs hover:scale-105 transition-transform" aria-label="Logout">
                    <x-icon name="o-power" />
                  </button>
                </form>
                            <a wire:navigate href="{{ route('app.dashboard') }}" class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                {{ __('Dashboard') }}
                            </a>
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

                {{-- Mobile Menu Toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden btn btn-ghost btn-circle btn-sm">
                    <x-icon x-show="!mobileMenuOpen" name="o-bars-3" class="w-6 h-6" />
                    <x-icon x-show="mobileMenuOpen" name="o-x-mark" class="w-6 h-6" x-cloak />
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <div class="px-4 py-4 space-y-2">
            {{-- Mobile Navigation Links --}}
            <a href="{{ route('web.campaigns') }}" wire:navigate @click="mobileMenuOpen = false" class="block px-4 py-3 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition">
                {{ __('Campaigns') }}
            </a>
            <a href="{{ route('web.home') }}#features" @click="mobileMenuOpen = false" class="block px-4 py-3 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition">
                {{ __('Features') }}
            </a>
            <a href="{{ route('web.home') }}#reports" @click="mobileMenuOpen = false" class="block px-4 py-3 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition">
                {{ __('Reports') }}
            </a>

            {{-- Mobile Auth Buttons --}}
            @if (Route::has('login'))
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    @auth
                        {{-- Mobile Badges --}}
                         <div class="grid grid-cols-2 gap-2 mb-2">
                            <a wire:navigate href="{{ route('app.chat') }}" @click="mobileMenuOpen = false" class="flex items-center justify-center gap-2 px-4 py-3 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition border border-gray-100 dark:border-gray-800">
                                <div class="indicator">
                                    <x-icon name="o-chat-bubble-left-right" class="w-5 h-5" />
                                    @if($this->unreadMessagesCount > 0)
                                        <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadMessagesCount }}</span>
                                    @endif
                                </div>
                                <span>{{ __('Messages') }}</span>
                            </a>
                            <a wire:navigate href="{{ route('app.notifications') }}" @click="mobileMenuOpen = false" class="flex items-center justify-center gap-2 px-4 py-3 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition border border-gray-100 dark:border-gray-800">
                                <div class="indicator">
                                    <x-icon name="o-bell" class="w-5 h-5" />
                                    @if($this->unreadNotificationsCount > 0)
                                        <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadNotificationsCount }}</span>
                                    @endif
                                </div>
                                <span>{{ __('Notifications') }}</span>
                            </a>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full btn btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white" aria-label="Logout">
                                {{ __('Logout') }}             
                            </button>
                        </form>
                        <a wire:navigate href="{{ route('app.dashboard') }}" @click="mobileMenuOpen = false" class="block pt-2 w-full btn bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a wire:navigate href="{{ route('login') }}" @click="mobileMenuOpen = false" class="block pt-2 w-full btn btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white">
                            {{ __('Log in') }}
                        </a>
                        @if (Route::has('register'))
                            <a wire:navigate href="{{ route('register') }}" @click="mobileMenuOpen = false" class="block pt-2 w-full btn bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                {{ __('Register') }}
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </div>
</nav>