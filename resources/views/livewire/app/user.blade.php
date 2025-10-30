<div class="space-y-6">
  <x-header title="Users" subtitle="Manage users, search, sort, and assign roles." separator />

  <x-card>
    <div class="grid md:grid-cols-4 gap-3 mb-4">
      <div class="md:col-span-2">
        <x-input wire:model.debounce.400ms="search" label="Search" icon="o-magnifying-glass" placeholder="Name or email..." />
      </div>
      <div>
        <x-select label="Filter by role" wire:model.live="roleFilter" :options="array_merge([[ 'id' => null, 'name' => 'All roles' ]], $this->roleOptions)" />
      </div>
      <div class="flex items-end justify-end">
        <x-button class="btn-primary" icon="o-plus" wire:click="create">New User</x-button>
      </div>
    </div>

    <div class="flex items-center justify-between mb-2">
      <div class="text-sm opacity-70">Sort: <span class="font-medium">{{ $sortField }}</span> <span class="badge badge-ghost">{{ strtoupper($sortDirection) }}</span></div>
      <x-select label="Per page" wire:model.live="perPage" :options="[[ 'id' => 10, 'name' => '10' ], [ 'id' => 25, 'name' => '25' ], [ 'id' => 50, 'name' => '50' ]]" />
    </div>

    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            <th class="cursor-pointer" wire:click="sortBy('name')">Name</th>
            <th class="cursor-pointer" wire:click="sortBy('email')">Email</th>
            <th>Roles</th>
            <th class="cursor-pointer" wire:click="sortBy('created_at')">Created</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $u)
            <tr>
              <td class="font-medium">{{ $u->name }}</td>
              <td class="text-base-content/80">{{ $u->email }}</td>
              <td class="space-x-1">
                @forelse($u->roles as $r)
                  <span class="badge badge-outline">{{ $r->name }}</span>
                @empty
                  <span class="text-xs opacity-60">—</span>
                @endforelse
              </td>
              <td class="text-sm text-base-content/70">{{ optional($u->created_at)->diffForHumans() }}</td>
              <td class="text-right space-x-1">
                <x-button class="btn-ghost btn-sm" icon="o-pencil-square" wire:click="edit({{ $u->id }})">Edit</x-button>
                <x-button class="btn-ghost btn-sm text-error" icon="o-trash" wire:click="confirmDelete({{ $u->id }})">Delete</x-button>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-base-content/60 py-6">No users found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $users->onEachSide(1)->links() }}</div>
  </x-card>

  <!-- Create/Edit Modal -->
  <x-modal wire:model="showForm" title="{{ $isEditing ? 'Edit User' : 'New User' }}" subtitle="Create or update a user and assign roles.">
    <div class="space-y-4">
      <div class="grid md:grid-cols-2 gap-4">
        <x-input label="Name" wire:model.defer="name" />
        <x-input label="Email" wire:model.defer="email" type="email" />
      </div>
      <div class="grid md:grid-cols-2 gap-4">
        <x-input label="Password" wire:model.defer="password" type="password" :placeholder="$isEditing ? 'Leave blank to keep current' : ''" />
        <x-input label="Confirm Password" wire:model.defer="password_confirmation" type="password" />
      </div>
      <div>
        <x-select
          label="Roles"
          wire:model.live="roles"
          :options="$this->roleOptions"
          multiple
          placeholder="Select roles..."
        />
      </div>
    </div>
    <x-slot:actions>
      <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('showForm', false)">Cancel</x-button>
      <x-button class="btn-primary" icon="o-check" wire:click="save" spinner="save">Save</x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Delete confirm modal -->
  <x-modal wire:model="confirmingDeleteId" title="Delete user" subtitle="This action cannot be undone.">
    <div class="space-y-2">
      <p>Are you sure you want to delete this user?</p>
    </div>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingDeleteId', null)" icon="o-x-mark">Cancel</x-button>
      <x-button class="btn-error" wire:click="deleteConfirmed" icon="o-trash">Delete</x-button>
    </x-slot:actions>
  </x-modal>
</div>
