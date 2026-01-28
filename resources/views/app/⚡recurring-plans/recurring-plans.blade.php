<div x-data="recurringPlans()" class="m-0 md:m-2">
    <x-header :title="__('Recurring Plans')" :subtitle="__('Manage recurring donations and schedules')" separator>
        <x-slot:middle class="!justify-end">
            <div class="flex items-center gap-2">
                <x-button
                    @click="filtersOpen = !filtersOpen"
                    icon="o-funnel"
                    class="btn-ghost btn-sm gap-2"
                    x-bind:class="{ 'btn-active': filtersOpen }">
                    {{ __('Filters') }}
                    @if($search || $itemStatus || $campaignId)
                        <x-badge value="{{ ($search ? 1 : 0) + ($itemStatus ? 1 : 0) + ($campaignId ? 1 : 0) }}" class="badge-primary badge-sm" />
                    @endif
                </x-button>
                @can('recurring-plans.create')
                    <x-button @click="toggleModal" icon="o-plus" class="btn-primary btn-sm">
                        <span class="hidden sm:inline">{{ __('Add Plan') }}</span>
                        <span class="sm:hidden">{{ __('Add') }}</span>
                    </x-button>
                @endcan
            </div>
        </x-slot:middle>
    </x-header>

    <div x-show="filtersOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="mb-6">
        <x-card class="border-l-4 border-primary">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ __('Filter & Search') }}
                </h3>
                @if($search || $itemStatus || $campaignId)
                    <x-button
                        wire:click="$set('search', ''); $set('itemStatus', null); $set('campaignId', null)"
                        icon="o-x-mark"
                        class="btn-ghost btn-xs">
                        {{ __('Clear All') }}
                    </x-button>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <x-input
                        wire:model.live.debounce.500ms="search"
                        :label="__('Search')"
                        :placeholder="__('Search users or campaigns...')"
                        icon="o-magnifying-glass"
                        clearable />
                </div>
                <div>
                    <x-select
                        wire:model.live="itemStatus"
                        :label="__('Status')"
                        :options="[
                            ['id' => null, 'name' => __('All Status')],
                            ['id' => 'active', 'name' => __('Active')],
                            ['id' => 'paused', 'name' => __('Paused')],
                            ['id' => 'cancelled', 'name' => __('Cancelled')]
                        ]"
                        icon="o-funnel" />
                </div>
                <div>
                    <x-select
                        wire:model.live="campaignId"
                        :label="__('Campaign')"
                        :options="array_merge([[ 'id' => null, 'name' => __('All Campaigns') ]], $this->campaigns->map(fn ($c) => ['id' => $c->id, 'name' => $c->title])->toArray())"
                        icon="o-flag" />
                </div>
                <div>
                    <x-select
                        wire:model.live="itemPerPage"
                        :label="__('Per Page')"
                        :options="[
                            ['id' => 10, 'name' => '10'],
                            ['id' => 25, 'name' => '25'],
                            ['id' => 50, 'name' => '50'],
                            ['id' => 100, 'name' => '100']
                        ]"
                        icon="o-bars-3" />
                </div>
            </div>
        </x-card>
    </div>

@if (count($selectedRows) > 0)
    <x-alert class="mb-6 alert-info shadow-sm rounded-lg" icon="o-information-circle">
        <div class="flex flex-wrap items-center justify-between gap-3 w-full">
            <span class="font-medium inline-flex items-center gap-2">
                {{ __(':count selected', ['count' => count($selectedRows)]) }}
                <x-badge value="{{ count($selectedRows) }}" class="badge-primary badge-sm" />
            </span>
            <div class="flex items-center gap-2">
                <x-button
                    @click="rows = []; selectPage = false"
                    wire:click="$set('selectedRows', [])"
                    icon="o-x-mark"
                    class="btn-ghost btn-sm">
                    {{ __('Clear Selection') }}
                </x-button>
                @can('recurring-plans.delete')
                    <x-button
                        @click="confirmDeleteMultiple({{ count($selectedRows) }})"
                        icon="o-trash"
                        class="btn-sm btn-error"
                        tooltip="{{ __('Delete Selected') }}">
                        {{ __('Delete Selected') }}
                    </x-button>
                @endcan
            </div>
        </div>
    </x-alert>
@endif

    <x-card class="!p-0 !mx-0">
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr class="text-center items-center font-semibold">
                        <th>
                            <x-checkbox
                                x-model="selectPage"
                                wire:model.live="selectPageRows" />
                        </th>
                        <x-field :OB="$orderBy" :OD="$orderDirection" :field="'campaign_id'">@lang('campaign')</x-field>
                        <x-field :OB="$orderBy" :OD="$orderDirection" :field="'user_id'">@lang('subscriber')</x-field>
                        <x-field :OB="$orderBy" :OD="$orderDirection" :field="'amount'">@lang('amount')</x-field>
                        <x-field :OB="$orderBy" :OD="$orderDirection" :field="'interval'">@lang('schedule')</x-field>
                        <x-field :OB="$orderBy" :OD="$orderDirection" :field="'status'">@lang('status')</x-field>
                        <x-field :OB="$orderBy" :OD="$orderDirection" :field="'next_run_at'">@lang('next run')</x-field>
                        <x-field>@lang('donations')</x-field>
                        <x-field>@lang('action')</x-field>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->items as $item)
                        @php
                            $currency = $item->currency ?: 'BDT';
                            $currencySymbol = $currency === 'BDT' ? '৳' : $currency;
                            $statusClass = match ($item->status) {
                                'active' => 'bg-emerald-100/60 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300',
                                'paused' => 'bg-amber-100/60 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                                'cancelled' => 'bg-red-100/60 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            };
                            $weekdays = [
                                __('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'),
                            ];
                            $scheduleLabel = $item->interval === 'weekly'
                                ? ($weekdays[$item->day_of_week] ?? __('Weekly'))
                                : __('Day :day', ['day' => $item->day_of_month ?? 1]);
                        @endphp
                        <tr wire:key="item-{{ $item->id }}" id="item-id-{{ $item->id }}" class="text-center items-center" :class="{'bg-base-300 rounded-md': rows.includes('{{$item->id}}') }">
                            <td>
                                <x-checkbox
                                    x-model="rows"
                                    value="{{ $item->id }}"
                                    wire:model.live="selectedRows" />
                            </td>
                            <td class="max-w-40 truncate">
                                <p class="font-medium table-sm">{{ $item->campaign?->title ?? '-' }}</p>
                                <p class="text-xs opacity-60">#{{ $item->id }}</p>
                            </td>
                            <td class="max-w-48 truncate">
                                <p class="font-medium table-sm">{{ $item->user?->name ?? __('Guest') }}</p>
                                <p class="text-xs opacity-60">{{ $item->user?->email ?? '-' }}</p>
                            </td>
                            <td>
                                <div class="text-xs">{{ $currencySymbol }} {{ number_format($item->amount, 0) }}</div>
                                <div class="text-xs opacity-60">{{ strtoupper($currency) }}</div>
                            </td>
                            <td>
                                <div class="text-xs font-semibold">{{ ucfirst($item->interval) }}</div>
                                <div class="text-xs opacity-60">{{ $scheduleLabel }}</div>
                            </td>
                            <td>
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span class="text-xs font-normal">{{ ucfirst($item->status) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs">{{ $item->next_run_at?->format('M d, Y') ?? '-' }}</div>
                                <div class="text-xs opacity-60">{{ $item->last_run_at?->format('M d, Y') ?? __('Not run') }}</div>
                            </td>
                            <td>
                                <div class="text-xs">{{ number_format($item->donations_count ?? 0) }}</div>
                                <div class="text-xs opacity-60">{{ __('Donations') }}</div>
                            </td>
                            <td class="text-center items-center">
                                <div class="flex items-center justify-center gap-2">
                                    @can('recurring-plans.edit')
                                        <x-button
                                            @click="editModal({{ $item->id }})"
                                            icon="o-pencil"
                                            class="btn-ghost btn-sm"
                                            tooltip="{{ __('Edit') }}" />
                                    @endcan
                                    @can('recurring-plans.delete')
                                        <x-button
                                            @click="confirmDelete({{ $item->id }}, '{{ addslashes($item->campaign?->title ?? 'Plan') }}')"
                                            icon="o-trash"
                                            class="btn-ghost btn-sm text-error"
                                            tooltip="{{ __('Delete') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-base-content/60">
                                <x-icon name="o-arrow-path" class="w-12 h-12 mx-auto mb-2 opacity-20" />
                                <div>{{ __('No recurring plans found') }}</div>
                                @if($search || $itemStatus || $campaignId)
                                    <x-button
                                        wire:click="$set('search', ''); $set('itemStatus', null); $set('campaignId', null)"
                                        class="mt-2 btn-sm btn-ghost">
                                        {{ __('Clear filters') }}
                                    </x-button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->items->links() }}
        </div>
    </x-card>

    <div x-show="isOpen"
         x-cloak
         @click.self="closeModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/50 backdrop-blur-sm">
        <div @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-4xl bg-white rounded-lg shadow-xl dark:bg-gray-800">

            <div class="flex items-center justify-between p-6 border-b dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    <span x-show="!editMode">{{ __('Add Recurring Plan') }}</span>
                    <span x-show="editMode">{{ __('Recurring Plan Details') }}</span>
                </h3>
                <button @click="closeModal" type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-icon name="o-x-mark" class="w-6 h-6" />
                </button>
            </div>

            <form @submit.prevent="editMode ? $wire.editData() : $wire.saveData()">
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    @php
                        $detailCurrency = $plan?->currency ?: 'BDT';
                        $detailSymbol = $detailCurrency === 'BDT' ? '৳' : $detailCurrency;
                    @endphp

                    @if($plan)
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="p-4 border rounded-lg dark:border-gray-700">
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Campaign') }}</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $plan?->campaign?->title ?? '-' }}</p>
                                <p class="text-xs opacity-60">#{{ $plan?->campaign_id ?? '-' }}</p>
                            </div>
                            <div class="p-4 border rounded-lg dark:border-gray-700">
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Subscriber') }}</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $plan?->user?->name ?? __('Guest') }}</p>
                                <p class="text-xs opacity-60">{{ $plan?->user?->email ?? '-' }}</p>
                            </div>
                            <div class="p-4 border rounded-lg dark:border-gray-700">
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Amount') }}</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $detailSymbol }} {{ $plan?->amount ? number_format($plan->amount, 0) : '-' }}</p>
                                <p class="text-xs opacity-60">{{ strtoupper($detailCurrency) }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-select
                            wire:model.defer="planCampaignId"
                            :label="__('Campaign')"
                            icon="o-flag"
                            :options="$this->campaigns->map(fn ($c) => ['id' => $c->id, 'name' => $c->title])->toArray()"
                            required />

                        <x-select
                            wire:model.defer="userId"
                            :label="__('Subscriber')"
                            icon="o-user"
                            :options="$this->users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name.' · '.$u->email])->toArray()"
                            required />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-input
                            wire:model.defer="amount"
                            type="number"
                            :label="__('Amount')"
                            icon="o-currency-dollar"
                            min="1" />

                        <x-input
                            wire:model.defer="currency"
                            type="text"
                            :label="__('Currency')"
                            icon="o-banknotes"
                            maxlength="3" />
                    </div>

                    <div x-data="{ interval: @entangle('interval').live }" class="grid gap-4 md:grid-cols-3">
                        <x-select
                            wire:model.live="interval"
                            :label="__('Interval')"
                            icon="o-arrow-path"
                            :options="[
                                ['id' => 'weekly', 'name' => __('Weekly')],
                                ['id' => 'monthly', 'name' => __('Monthly')]
                            ]" />

                        <div x-show="interval === 'weekly'" x-cloak>
                            <x-select
                                wire:model.defer="day_of_week"
                                :label="__('Day of Week')"
                                icon="o-calendar"
                                :options="[
                                    ['id' => 0, 'name' => __('Sunday')],
                                    ['id' => 1, 'name' => __('Monday')],
                                    ['id' => 2, 'name' => __('Tuesday')],
                                    ['id' => 3, 'name' => __('Wednesday')],
                                    ['id' => 4, 'name' => __('Thursday')],
                                    ['id' => 5, 'name' => __('Friday')],
                                    ['id' => 6, 'name' => __('Saturday')]
                                ]" />
                        </div>

                        <div x-show="interval === 'monthly'" x-cloak>
                            <x-input
                                wire:model.defer="day_of_month"
                                type="number"
                                :label="__('Day of Month')"
                                icon="o-calendar-days"
                                min="1"
                                max="31" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-select
                            wire:model.defer="status"
                            :label="__('Status')"
                            :options="[
                                ['id' => 'active', 'name' => __('Active')],
                                ['id' => 'paused', 'name' => __('Paused')],
                                ['id' => 'cancelled', 'name' => __('Cancelled')]
                            ]"
                            icon="o-funnel" />

                        <x-input
                            wire:model.defer="next_run_at"
                            type="datetime-local"
                            :label="__('Next Run At')"
                            icon="o-calendar" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-input
                            wire:model.defer="starts_at"
                            type="datetime-local"
                            :label="__('Starts At')"
                            icon="o-play" />

                        <x-input
                            wire:model.defer="ends_at"
                            type="datetime-local"
                            :label="__('Ends At')"
                            icon="o-stop" />
                    </div>
                </div>

                <div class="flex items-center gap-3 p-6 border-t dark:border-gray-700">
                    <x-button
                        wire:loading.remove
                        wire:target="editData,saveData"
                        type="submit"
                        class="btn-primary"
                        icon="o-check">
                        <span x-show="!editMode">{{ __('Create Plan') }}</span>
                        <span x-show="editMode">{{ __('Update Plan') }}</span>
                    </x-button>
                    <x-button
                        wire:loading
                        wire:target="editData,saveData"
                        type="button"
                        disabled
                        class="btn-primary">
                        <span class="loading loading-spinner"></span>
                        {{ __('Processing...') }}
                    </x-button>
                    <x-button
                        @click="closeModal"
                        type="button"
                        class="btn-ghost"
                        icon="o-x-mark">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="confirmOpen"
         x-cloak
         @click.self="closeConfirm"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md bg-white rounded-lg shadow-xl dark:bg-gray-800">

            <div class="flex items-center gap-3 p-6 border-b dark:border-gray-700">
                <div class="flex items-center justify-center w-12 h-12 rounded-full"
                     :class="{
                         'bg-red-100 dark:bg-red-900/20': confirmType === 'danger',
                         'bg-yellow-100 dark:bg-yellow-900/20': confirmType === 'warning',
                         'bg-blue-100 dark:bg-blue-900/20': confirmType === 'info'
                     }">
                    <x-icon name="o-exclamation-triangle"
                            class="w-6 h-6"
                            x-bind:class="{
                                'text-red-600 dark:text-red-400': confirmType === 'danger',
                                'text-yellow-600 dark:text-yellow-400': confirmType === 'warning',
                                'text-blue-600 dark:text-blue-400': confirmType === 'info'
                            }" />
                </div>
                <h3 class="flex-1 text-lg font-semibold text-gray-900 dark:text-white" x-text="confirmTitle"></h3>
                <button @click="closeConfirm"
                        type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-icon name="o-x-mark" class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="confirmMessage"></p>
            </div>

            <div class="flex items-center justify-end gap-3 p-6 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <x-button
                    @click="closeConfirm"
                    type="button"
                    class="btn-ghost">
                    {{ __('Cancel') }}
                </x-button>
                <x-button
                    @click="executeConfirm"
                    type="button"
                    class="gap-2"
                    x-bind:class="{
                        'btn-error': confirmType === 'danger',
                        'btn-warning': confirmType === 'warning',
                        'btn-info': confirmType === 'info'
                    }">
                    <x-icon name="o-trash" class="w-4 h-4" />
                    <span x-show="confirmType === 'danger'">{{ __('Delete') }}</span>
                    <span x-show="confirmType === 'warning'">{{ __('Proceed') }}</span>
                    <span x-show="confirmType === 'info'">{{ __('Confirm') }}</span>
                </x-button>
            </div>
        </div>
    </div>

@script
<script>
    Alpine.data('recurringPlans', () => ({
        isOpen: false,
        editMode: false,
        selectPage: false,
        rows: [],
        filtersOpen: false,

        confirmOpen: false,
        confirmTitle: '',
        confirmMessage: '',
        confirmAction: null,
        confirmType: 'danger',

        init() {
            $wire.on('dataUpdated', (e) => {
                this.isOpen = false;
                this.editMode = false;

                $nextTick(() => {
                    const element = document.getElementById(e.dataId);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.classList.add('animate-pulse');

                        setTimeout(() => {
                            element.classList.remove('animate-pulse');
                        }, 5000);
                    }
                });
            });

            $wire.on('dataAdded', (e) => {
                this.isOpen = false;
                this.editMode = false;

                $nextTick(() => {
                    const element = document.getElementById(e.dataId);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.classList.add('animate-pulse');

                        setTimeout(() => {
                            element.classList.remove('animate-pulse');
                        }, 5000);
                    }
                });
            });
        },

        toggleModal() {
            this.isOpen = !this.isOpen;
            if (!this.isOpen) {
                this.editMode = false;
                $wire.resetData();
            }
        },

        closeModal() {
            this.isOpen = false;
            this.editMode = false;
            $wire.resetData();
        },

        editModal(id) {
            $wire.loadData(id);
            this.isOpen = true;
            this.editMode = true;
        },

        confirmDelete(id, title = 'Delete Plan') {
            this.confirmTitle = '{{ __("Confirm Deletion") }}';
            this.confirmMessage = `{{ __("Are you sure you want to delete") }} "${title}"? {{ __("This action cannot be undone.") }}`;
            this.confirmType = 'danger';
            this.confirmAction = () => {
                $wire.deleteSingle(id);
                this.closeConfirm();
            };
            this.confirmOpen = true;
        },

        confirmDeleteMultiple(count) {
            this.confirmTitle = '{{ __("Confirm Bulk Deletion") }}';
            this.confirmMessage = `{{ __("Are you sure you want to delete") }} ${count} {{ __("selected plans?") }} {{ __("This action cannot be undone.") }}`;
            this.confirmType = 'danger';
            this.confirmAction = () => {
                $wire.deleteMultiple();
                this.closeConfirm();
            };
            this.confirmOpen = true;
        },

        executeConfirm() {
            if (this.confirmAction) {
                this.confirmAction();
            }
        },

        closeConfirm() {
            this.confirmOpen = false;
            this.confirmTitle = '';
            this.confirmMessage = '';
            this.confirmAction = null;
            this.confirmType = 'danger';
        }
    }));
</script>
@endscript
</div>
