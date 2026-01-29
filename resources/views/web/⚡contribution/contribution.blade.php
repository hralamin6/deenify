@section('title', $contribution->title)
@section('image', getImage($contribution, 'cover', 'thumb'))
@section('description', Str::limit(strip_tags($contribution->description), 333))
@php
    $shareUrl = urlencode(request()->fullUrl());
    $shareText = urlencode($contribution->title);
@endphp
<div x-data="contributionDetail()" class="min-h-screen bg-slate-50 dark:bg-gray-950">
    {{-- Top Navigation Breadcrumb --}}
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('web.home') }}" wire:navigate class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">{{ __('Home') }}</a>
                <x-icon name="o-chevron-right" class="w-4 h-4" />
                <a href="{{ route('web.contributions') }}" wire:navigate class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">{{ __('Contributions') }}</a>
                <x-icon name="o-chevron-right" class="w-4 h-4" />
                <span class="text-gray-900 dark:text-white font-medium truncate">{{ $contribution->title }}</span>
            </nav>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="grid gap-12 lg:grid-cols-12">
            
            {{-- Left Content: Gallery & Description --}}
            <div class="lg:col-span-8 space-y-10">
                
                {{-- Main Content Card --}}
                <div class="bg-white dark:bg-gray-900 rounded-[3rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                    {{-- Hero Image --}}
                    <div class="relative h-[30rem] sm:h-[35rem] w-full overflow-hidden">
                        <img src="{{ getImage($contribution, 'cover', 'thumb') }}" 
                             alt="{{ $contribution->title }}"
                             class="w-full h-full object-cover" />
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                        
                        <div class="absolute bottom-10 left-10 right-10">
                            <h1 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight drop-shadow-md">
                                {{ $contribution->title }}
                            </h1>
                        </div>
                    </div>

                    <div class="p-8 sm:p-12">
                        {{-- Meta Info Bar --}}
                        <div class="flex flex-wrap items-center gap-6 mb-10 pb-10 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl text-emerald-600 dark:text-emerald-400 shadow-sm">
                                    <x-icon name="o-currency-bangladeshi" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">{{ __('Amount Deployed') }}</p>
                                    <p class="text-xl font-bold dark:text-gray-100">৳ {{ number_format($contribution->amount, 0) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/30 rounded-2xl text-indigo-600 dark:text-indigo-400 shadow-sm">
                                    <x-icon name="o-calendar" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">{{ __('Date') }}</p>
                                    <p class="text-xl font-bold dark:text-gray-100">{{ $contribution->date?->format('F d, Y') ?? 'TBA' }}</p>
                                </div>
                            </div>
                            @if($contribution->location)
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-rose-50 dark:bg-rose-950/30 rounded-2xl text-rose-600 dark:text-rose-400 shadow-sm">
                                    <x-icon name="o-map-pin" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">{{ __('Location') }}</p>
                                    <p class="text-xl font-bold dark:text-gray-100">{{ $contribution->location }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Description --}}
                        <article class="prose prose-lg dark:prose-invert max-w-none">
                            <div class="p-6 bg-slate-50 dark:bg-gray-800/50 rounded-3xl border-l-8 border-emerald-500 mb-8 italic text-slate-700 dark:text-slate-300">
                                {{ __('Summary of achievement and impact for this contribution.') }}
                            </div>
                            <div class="text-gray-700 dark:text-gray-300 leading-relaxed space-y-6 whitespace-pre-line">
                                {{ $contribution->description }}
                            </div>
                        </article>
                    </div>
                </div>

                {{-- Gallery Section --}}
                @if($contribution->getMedia('gallery')->count() > 0)
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold flex items-center gap-3 dark:text-white">
                        <x-icon name="o-camera" class="w-8 h-8 text-emerald-500" />
                        {{ __('Visual Impact Gallery') }}
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($contribution->getMedia('gallery') as $index => $media)
                        <div class="group relative aspect-square overflow-hidden rounded-[2rem] shadow-lg cursor-pointer transition-all hover:scale-[1.02] hover:shadow-2xl"
                             @click="openLightbox({{ $index }})">
                            <img src="{{ $media->getUrl('thumb') }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                                 alt="Gallery item {{ $index + 1 }}">
                            <div class="absolute inset-0 bg-emerald-900/0 group-hover:bg-emerald-900/20 transition-all duration-300 flex items-center justify-center">
                                <div class="p-3 bg-white/20 backdrop-blur-md rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                    <x-icon name="o-magnifying-glass-plus" class="w-6 h-6 text-white" />
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Sidebar --}}
            <div class="lg:col-span-4 space-y-10">
                
                {{-- Share & Support Card --}}
                <div class="rounded-[2.5rem] bg-white dark:bg-gray-800 p-8 shadow-xl border border-gray-200 dark:border-gray-700 relative transition-all duration-300" x-data="{ open: false }">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-200 dark:shadow-none">
                            <x-icon name="o-share" class="w-8 h-8 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Share this Impact') }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Spread the word about our progress. Your advocacy motivates more people to join our cause.') }}
                        </p>
                    </div>

                    <div class="space-y-3 relative">
                        {{-- Share Button & Dropdown --}}
                        <div class="relative">
                            <button @click="open = !open" @keydown.escape.window="open = false" class="w-full btn btn-outline border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-400 dark:text-emerald-400">
                                <x-icon name="o-share" class="w-5 h-5" />
                                {{ __('Share Story') }}
                            </button>

                            <div x-cloak x-show="open" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                                 x-transition:leave="transition ease-in duration-150" 
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                 x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                                 @click.outside="open = false" 
                                 class="absolute right-0 bottom-full mb-4 w-72 p-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl shadow-2xl z-50">
                                
                                <div class="px-2 pb-2 mb-2 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Spread the Word') }}</p>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-blue-600 dark:text-blue-400">Facebook</span>
                                    </a>
                                    <a href="https://x.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                                        <svg class="w-6 h-6 text-gray-900 dark:text-white group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.134l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-gray-600 dark:text-gray-400">X</span>
                                    </a>
                                    <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">WhatsApp</span>
                                    </a>
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-sky-50 dark:bg-sky-900/20 hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-sky-600 dark:text-sky-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-sky-600 dark:text-sky-400">LinkedIn</span>
                                    </a>
                                    <a href="https://www.reddit.com/submit?url={{ $shareUrl }}&title={{ $shareText }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-orange-600 dark:text-orange-400">Reddit</span>
                                    </a>
                                    <a href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 hover:bg-cyan-100 dark:hover:bg-cyan-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M20.665 3.717l-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.44z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-cyan-600 dark:text-cyan-400">Telegram</span>
                                    </a>
                                    <a href="mailto:?subject={{ $shareText }}&body={{ $shareText }}%0A%0A{{ $shareUrl }}" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                                        <x-icon name="o-envelope" class="w-6 h-6 text-gray-600 dark:text-gray-400 group-hover:scale-110 transition-transform" />
                                        <span class="mt-1 text-[10px] font-bold text-gray-600 dark:text-gray-400">Email</span>
                                    </a>
                                    <a href="https://www.pinterest.com/pin/create/button/?url={{ $shareUrl }}&description={{ $shareText }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.65 0-5.789 2.733-5.789 5.558 0 1.103.425 2.286.953 2.922.105.126.12.235.089.425-.098.408-.312 1.272-.355 1.45-.062.253-.2.308-.464.187-1.731-.806-2.527-2.983-2.527-4.809 0-3.912 3.109-7.502 8.358-7.502 4.385 0 7.272 3.166 7.272 6.551 0 3.909-2.203 6.945-5.263 6.945-1.029 0-1.996-.534-2.328-1.164l-.633 2.411c-.229.89-1.724 3.864-2.306 5.176.883.254 1.831.395 2.812.395 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-red-600 dark:text-red-400">Pinterest</span>
                                    </a>
                                    <button type="button" @click="
                                        const title = decodeURIComponent('{{ $shareText }}');
                                        const url = decodeURIComponent('{{ $shareUrl }}');
                                        if (navigator.share) {
                                            navigator.share({ title, text: title, url }).catch(()=>{});
                                        } else {
                                            alert('Sharing not supported on this browser.');
                                        }
                                    " class="flex flex-col items-center justify-center p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors group">
                                        <x-icon name="o-device-phone-mobile" class="w-6 h-6 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform" />
                                        <span class="mt-1 text-[10px] font-bold text-purple-600 dark:text-purple-400">{{ __('Device') }}</span>
                                    </button>
                                </div>

                                <button @click="navigator.clipboard.writeText(decodeURIComponent('{{ $shareUrl }}')); alert('Link copied!');" class="mt-3 w-full py-2.5 px-4 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-emerald-300 dark:hover:border-emerald-500 flex items-center justify-center gap-2 transition-all group/copy bg-gray-50/50 dark:bg-transparent">
                                    <x-icon name="o-link" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 group-hover/copy:scale-110 transition-transform" />
                                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ __('Copy Contribution Link') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- Donate Link matching campaign style --}}
                        <a href="{{ route('web.campaigns') }}" 
                           wire:navigate
                           class="block w-full btn bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white border-0 shadow-lg">
                            <x-icon name="o-heart" class="w-5 h-5" />
                            {{ __('Donate Today') }}
                        </a>
                    </div>
                </div>

                {{-- Stats Card --}}
                <div class="bg-white dark:bg-gray-900 rounded-[3rem] p-10 border border-gray-100 dark:border-gray-800 shadow-xl">
                    <h3 class="text-xl font-bold mb-8 dark:text-white">{{ __('Project Verification') }}</h3>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                                <x-icon name="o-check-badge" class="w-5 h-5" />
                            </div>
                            <div>
                                <h4 class="font-bold text-sm dark:text-gray-200">{{ __('Impact Verified') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ __('This record represents a physical implementation verified by our ground team.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="p-2 bg-blue-50 dark:bg-blue-950/30 rounded-lg text-blue-600 dark:text-blue-400">
                                <x-icon name="o-shield-check" class="w-5 h-5" />
                            </div>
                            <div>
                                <h4 class="font-bold text-sm dark:text-gray-200">{{ __('Transparent Funding') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ __('The funds used for this project matched the specific allocations from donor contributions.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Stories Section --}}
        @if($this->similarContributions->count() > 0)
        <div class="mt-24 pt-16 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-3xl font-bold dark:text-white">{{ __('Other Impact Stories') }}</h2>
                <a href="{{ route('web.contributions') }}" wire:navigate class="btn btn-ghost btn-sm text-emerald-600 dark:text-emerald-400">
                    {{ __('View All') }}
                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                </a>
            </div>
            
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->similarContributions as $item)
                <div class="group bg-white dark:bg-gray-900 rounded-[2.5rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all border border-gray-100 dark:border-gray-800">
                    <div class="relative h-56 overflow-hidden">
                        <img src="{{ getImage($item, 'cover', 'thumb') }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        <div class="absolute top-4 right-4 p-2 bg-white/90 dark:bg-gray-900/90 rounded-xl text-xs font-bold shadow-sm">
                            {{ $item->date?->format('M Y') }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-lg mb-2 line-clamp-1 dark:text-gray-100">{{ $item->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-6">{{ $item->description }}</p>
                        <a href="{{ route('web.contribution', $item->slug) }}" wire:navigate class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 group/link">
                            {{ __('Learn more') }}
                            <x-icon name="o-chevron-right" class="w-4 h-4 group-hover/link:translate-x-1 transition-transform" />
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Lightbox Modal (Simplistic for now) --}}
    <div x-show="lightboxOpen" 
         x-cloak
         class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center p-4 backdrop-blur-xl"
         @click="lightboxOpen = false"
         @keydown.escape.window="lightboxOpen = false">
        
        <x-button icon="o-x-mark" 
                  class="absolute top-8 right-8 btn-circle btn-ghost text-white scale-150" 
                  @click="lightboxOpen = false" />
        
        <div class="relative max-w-7xl max-h-[90vh] w-full flex items-center justify-center" @click.stop>
            <img :src="gallery[activeIndex].url" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl" />
            
            <div class="absolute -bottom-16 left-0 right-0 text-center text-white/80 font-medium">
                <span x-text="activeIndex + 1"></span> / <span x-text="gallery.length"></span>
            </div>
            
            <button @click="prev" class="absolute left-4 p-4 bg-white/10 hover:bg-white/20 rounded-full text-white transition-all">
                <x-icon name="o-chevron-left" class="w-8 h-8" />
            </button>
            
            <button @click="next" class="absolute right-4 p-4 bg-white/10 hover:bg-white/20 rounded-full text-white transition-all">
                <x-icon name="o-chevron-right" class="w-8 h-8" />
            </button>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('contributionDetail', () => ({
        lightboxOpen: false,
        activeIndex: 0,
        gallery: @js($contribution->getMedia('gallery')->map(fn($m) => ['url' => $m->getUrl()])),

        openLightbox(index) {
            this.activeIndex = index;
            this.lightboxOpen = true;
        },

        next() {
            this.activeIndex = (this.activeIndex + 1) % this.gallery.length;
        },

        prev() {
            this.activeIndex = (this.activeIndex - 1 + this.gallery.length) % this.gallery.length;
        }
    }));
</script>
@endscript
