<div>
    <section class="relative overflow-hidden py-10 sm:py-14">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-sky-300/30 dark:bg-sky-600/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/20 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-sky-100 dark:bg-sky-900/30 border border-sky-200 dark:border-sky-800">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-sky-700 dark:text-sky-300 uppercase tracking-wider">{{ __('Notifications') }}</span>
                </div>
                <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                    {{ __('Your Notifications') }}
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('Stay up to date with messages, updates, and system alerts.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <button class="btn btn-sm {{ $activeTab === 'center' ? 'btn-primary' : 'btn-ghost' }}" wire:click="$set('activeTab', 'center')">
                    <x-icon name="o-inbox" class="w-4 h-4" />
                    {{ __('Center') }}
                    @if($unreadCount > 0)
                        <x-badge value="{{ $unreadCount }}" class="badge-error badge-sm ml-2" />
                    @endif
                </button>
                <button class="btn btn-sm {{ $activeTab === 'preferences' ? 'btn-primary' : 'btn-ghost' }}" wire:click="$set('activeTab', 'preferences')">
                    <x-icon name="o-cog-6-tooth" class="w-4 h-4" />
                    {{ __('Preferences') }}
                </button>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Manage delivery and privacy settings') }}
            </div>
        </div>

        @if($activeTab === 'center')
            <div class="mt-6 space-y-6">
                <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-xl">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <button class="btn btn-xs {{ $selectedFilter === 'all' ? 'btn-primary' : 'btn-ghost' }}" wire:click="$set('selectedFilter', 'all')">
                                {{ __('All') }}
                            </button>
                            <button class="btn btn-xs {{ $selectedFilter === 'unread' ? 'btn-primary' : 'btn-ghost' }}" wire:click="$set('selectedFilter', 'unread')">
                                {{ __('Unread') }}
                                @if($unreadCount > 0)
                                    <x-badge value="{{ $unreadCount }}" class="badge-primary ml-2" />
                                @endif
                            </button>
                            <button class="btn btn-xs {{ $selectedFilter === 'read' ? 'btn-primary' : 'btn-ghost' }}" wire:click="$set('selectedFilter', 'read')">
                                {{ __('Read') }}
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-button :label="__('Mark All Read')" icon="o-check-circle" class="btn-primary btn-sm" wire:click="markAllAsRead" spinner />
                            <x-button :label="__('Delete All')" icon="o-trash" class="btn-error btn-sm" wire:click="deleteAll" wire:confirm="{{ __('Delete all notifications?') }}" spinner />
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($this->notifications as $notification)
                        @php
                            $data = $notification->data;
                            $type = $notification->type;
                            $isUnread = is_null($notification->read_at);
                            $isChatNotification = str_contains($type, 'NewMessageNotification');
                            $hasUrl = isset($data['url']) && !empty($data['url']);
                        @endphp

                        <div class="group relative rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg hover:shadow-2xl transition {{ $isUnread ? 'ring-1 ring-primary/30' : '' }}">
                            <a wire:click.prevent="markAsReadAndRedirect('{{ $notification->id }}', '{{ $hasUrl ? $data['url'] : route('web.notifications') }}')" href="{{ $hasUrl ? $data['url'] : route('web.notifications') }}" class="absolute inset-0 z-10" aria-label="{{ __('View notification') }}"></a>

                            <div class="flex items-start gap-4 relative pointer-events-none">
                                <div class="flex-shrink-0">
                                    @if($isChatNotification && isset($data['sender_avatar']))
                                        <div class="avatar {{ $isUnread ? 'online' : '' }}">
                                            <div class="w-12 h-12 rounded-full ring-2 ring-primary/20">
                                                <img src="{{ $data['sender_avatar'] }}" alt="{{ $data['sender_name'] ?? 'User' }}" />
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $iconName = $data['icon'] ?? 'o-bell';
                                            $iconType = $data['type'] ?? 'info';
                                            $iconClass = match($iconType) {
                                                'success' => 'text-success bg-success/10',
                                                'error' => 'text-error bg-error/10',
                                                'warning' => 'text-warning bg-warning/10',
                                                default => 'text-info bg-info/10',
                                            };
                                        @endphp
                                        <div class="p-3 rounded-2xl {{ $iconClass }}">
                                            <x-icon :name="$iconName" class="w-6 h-6" />
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-semibold text-base {{ $hasUrl ? 'group-hover:text-primary transition-colors' : '' }}">
                                                    {{ $isChatNotification ? ($data['sender_name'] ?? __('Unknown User')) : ($data['title'] ?? __('Notification')) }}
                                                </h3>
                                                @if($isUnread)
                                                    <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-base-content/80 mt-1 line-clamp-2">
                                                {{ $isChatNotification ? ($data['body'] ?? __('Sent you a message')) : ($data['message'] ?? '') }}
                                            </p>

                                            <div class="flex items-center gap-3 mt-3">
                                                <div class="flex items-center gap-1 text-xs text-base-content/60">
                                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                @if($isUnread)
                                                    <x-badge :value="__('New')" class="badge-primary badge-xs" />
                                                @endif
                                                @if($hasUrl)
                                                    <div class="flex items-center gap-1 text-xs text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <x-icon name="o-arrow-right" class="w-3.5 h-3.5" />
                                                        <span>{{ __('Open') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-2 {{ $hasUrl ? 'pointer-events-auto' : '' }}">
                                            @if($isUnread)
                                                <x-button icon="o-check" class="btn-ghost btn-sm btn-circle" wire:click="markAsRead('{{ $notification->id }}')" :tooltip="__('Mark as read')" spinner />
                                            @endif
                                            <x-button icon="o-trash" class="btn-ghost btn-sm btn-circle text-error hover:bg-error/10" wire:click="deleteNotification('{{ $notification->id }}')" wire:confirm="{{ __('Delete this notification?') }}" :tooltip="__('Delete')" spinner />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-8 text-center">
                            <x-icon name="o-bell-slash" class="w-16 h-16 mx-auto text-base-content/30" />
                            <h3 class="mt-4 text-lg font-semibold text-base-content/70">{{ __('No notifications') }}</h3>
                            <p class="mt-2 text-sm text-base-content/50">{{ __('You are all caught up!') }}</p>
                        </div>
                    @endforelse
                </div>

                @if($this->notifications->hasPages())
                    <div class="mt-6">{{ $this->notifications->links() }}</div>
                @endif
            </div>
        @endif

        @if($activeTab === 'preferences')
            <div class="mt-6 space-y-6">
                <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-xl">
                    <div class="alert alert-info">
                        <x-icon name="o-information-circle" class="w-5 h-5" />
                        <div class="text-sm">
                            <strong>{{ __('Push:') }}</strong> {{ __('Browser notifications') }} •
                            <strong>{{ __('Email:') }}</strong> {{ __('Email messages') }} •
                            <strong>{{ __('Database:') }}</strong> {{ __('In-app notifications') }}
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ __('Notification Preferences') }}</h3>
                        <div class="flex gap-2">
                            <x-button :label="__('Enable All')" icon="o-check-circle" class="btn-success btn-sm" wire:click="enableAll" spinner />
                            <x-button :label="__('Disable All')" icon="o-x-circle" class="btn-error btn-sm" wire:click="disableAll" spinner />
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-1/3">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ __('Category') }}</span>
                                            <span class="text-xs font-normal text-base-content/60">{{ __('Notification type') }}</span>
                                        </div>
                                    </th>
                                    <th class="text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <x-icon name="o-bell" class="w-5 h-5 text-primary" />
                                            <span class="text-xs font-semibold">{{ __('Push') }}</span>
                                            <span class="text-xs font-normal text-base-content/60">{{ __('Browser') }}</span>
                                        </div>
                                    </th>
                                    <th class="text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <x-icon name="o-envelope" class="w-5 h-5 text-secondary" />
                                            <span class="text-xs font-semibold">{{ __('Email') }}</span>
                                            <span class="text-xs font-normal text-base-content/60">{{ __('Inbox') }}</span>
                                        </div>
                                    </th>
                                    <th class="text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <x-icon name="o-inbox" class="w-5 h-5 text-accent" />
                                            <span class="text-xs font-semibold">{{ __('Database') }}</span>
                                            <span class="text-xs font-normal text-base-content/60">{{ __('In-app') }}</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category => $details)
                                    <tr class="hover">
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-semibold">{{ $details['name'] }}</span>
                                                <span class="text-xs text-base-content/60">{{ $details['description'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <x-toggle wire:model.live="preferences.{{ $category }}.push_enabled" wire:change="savePreferences" class="toggle-primary" />
                                        </td>
                                        <td class="text-center">
                                            <x-toggle wire:model.live="preferences.{{ $category }}.email_enabled" wire:change="savePreferences" class="toggle-secondary" />
                                        </td>
                                        <td class="text-center">
                                            <x-toggle wire:model.live="preferences.{{ $category }}.database_enabled" wire:change="savePreferences" class="toggle-accent" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
