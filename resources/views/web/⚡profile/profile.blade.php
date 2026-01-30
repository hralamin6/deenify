<div>
    <section class="relative overflow-hidden py-12 sm:py-16">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-purple-300/30 dark:bg-purple-600/20 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">{{ __('Account') }}</span>
                </div>
                <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                    {{ __('Your Profile') }}
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('Manage your personal info, security, and preferences in one place.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16">
        @include('app.⚡profile.profile')
    </div>
</div>
