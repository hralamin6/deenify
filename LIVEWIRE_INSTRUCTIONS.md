# Livewire Feature Development - Base Template

## Overview

This is a **base template guide** for creating new Livewire features in this application. Follow this structure when building CRUD components like Users, Pages, Posts, Products, etc.

All features benefit from **automatic activity logging** via the GlobalActivityObserver - no manual observer registration required!

## Table of Contents

1. [Quick Start Checklist](#quick-start-checklist)
2. [Step 1: Create the Model](#step-1-create-the-model)
3. [Step 2: Automatic Activity Logging](#step-2-automatic-activity-logging)
4. [Step 3: Create Livewire Component](#step-3-create-livewire-component)
5. [Step 4: Build the UI](#step-4-build-the-ui)
6. [Step 5: Testing](#step-5-testing)
7. [Best Practices](#best-practices)
8. [Example: Page Component](#example-page-component)

---

## Quick Start Checklist

When creating a new feature (e.g., "Post" management):

- [ ] Create model with migration, factory, and seeder
- [ ] Define fillable attributes and relationships
- [ ] Add scopes and helper methods to model
- [ ] Create Livewire component using Artisan
- [ ] Implement CRUD operations in component
- [ ] Build Blade view with table, filters, and form
- [ ] Write feature tests
- [ ] Run Pint for code formatting
- [ ] Test the feature in browser
- [ ] Verify activity logging works

**That's it!** Activity logging happens automatically - no observer registration needed.

---

## Step 1: Create the Model

### Using Artisan Command

```bash
# Create model with migration, factory, seeder, and controller
php artisan make:model Post -mfs --no-interaction

# Or create model with everything you need
php artisan make:model Product -a --no-interaction
```

### Model Structure Template

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class YourModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',           // Main identifier
        'slug',            // URL-friendly identifier
        'description',     // Content field
        'status',          // 'active', 'inactive', 'draft', etc.
        'order',           // Display order
        // Add your specific fields
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_active' => 'boolean',
            // Add your custom casts
        ];
    }

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title
        static::creating(function ($model) {
            if (empty($model->slug) && isset($model->title)) {
                $model->slug = Str::slug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add more relationships as needed

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRouteKeyName()
    {
        return 'id'; // or 'slug' if you prefer
    }
}
```

### Optional: Add Media Library Support

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class YourModel extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    // ...existing code...

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('gallery'); // Multiple files
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->quality(75)
            ->nonQueued();
    }

    public function getImageUrlAttribute()
    {
        $media = $this->getFirstMedia('featured_image');
        if ($media) {
            return $media->getUrl('thumb');
        }
        return 'https://placehold.co/400';
    }
}
```

---

## Step 2: Automatic Activity Logging

## Step 2: Automatic Activity Logging

### ✅ Zero Configuration Required!

**Your model is automatically observed** by the `GlobalActivityObserver`. Every create, update, and delete operation is logged automatically.

**You DON'T need to:**
- Create a custom observer
- Register observers in AppServiceProvider
- Manually call Activity::create()
- Write logging code

### What Gets Logged Automatically

#### When Any Model is Created
```php
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is amazing content...',
    'status' => 'draft',
]);

// ✅ Automatically logged:
// Description: "Post 'My First Post' was created"
// Attributes: All model data
// User: Current authenticated user
// IP & User Agent: Captured automatically
```

#### When Any Model is Updated
```php
$post->update(['status' => 'published']);

// ✅ Automatically logged:
// Description: "Post 'My First Post' was updated"
// Old values: Previous attributes
// New values: Changed attributes
// Changes tracked: Only modified fields
```

#### When Any Model is Deleted
```php
$post->delete();

// ✅ Automatically logged:
// Description: "Post 'My First Post' was deleted"
// Final state: All model data before deletion
```

### How It Works

1. **GlobalActivityObserver** (`app/Observers/GlobalActivityObserver.php`) listens to ALL Eloquent models
2. Registered in `AppServiceProvider::boot()` via Laravel's event system
3. Automatically detects model name and creates activity logs
4. Smart identifier detection uses: `name`, `title`, `email`, `username`, or `slug`

### Exclude a Model (If Needed)

Only exclude models if absolutely necessary (e.g., Session, Cache, Activity itself):

```php
// In app/Observers/GlobalActivityObserver.php
protected array $excludedModels = [
    Activity::class,
    \App\Models\Session::class,  // Add models to exclude
];
```

### View Activity Logs

- `/app/activities/feed/` - All activities with filters
- `/app/activities/my/` - Your personal activities
- Filter by model type (e.g., "posts", "products")

---

## Step 3: Create Livewire Component

## Step 3: Create Livewire Component

### Generate Component with Artisan

```bash
# Create a new Livewire component
php artisan make:livewire App\\Post --no-interaction

# This creates:
# - Class: app/Livewire/App/Post.php
# - View: resources/views/livewire/app/post.blade.php
```

---

## Component Class Template

Here's a complete, production-ready Livewire component template. Adapt it for your model:

```php
<?php

namespace App\Livewire\App;

use App\Models\YourModel; // Change this
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Your Models')] // Change this
#[Layout('layouts.app')]
class YourModelComponent extends Component // Change this
{
    use Toast;
    use WithPagination;
    use WithFileUploads; // Optional: if you have file uploads

    // ==========================================
    // TABLE STATE
    // ==========================================
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public ?string $statusFilter = null; // Adjust filters as needed

    // ==========================================
    // FORM STATE
    // ==========================================
    public ?int $selectedId = null;
    
    // Add your model's fields here
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public string $status = 'draft';
    public ?string $published_at = null;
    public int $order = 0;
    
    // Optional: File upload
    public $featuredImage = null;

    // ==========================================
    // UI STATE
    // ==========================================
    public bool $showForm = false;
    public bool $isEditing = false;
    public ?int $confirmingDeleteId = null;

    // ==========================================
    // QUERY STRING (for shareable URLs)
    // ==========================================
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => null],
    ];

    // ==========================================
    // VALIDATION RULES
    // ==========================================
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('your_table', 'slug')->ignore($this->selectedId),
            ],
            'description' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,active,inactive'],
            'published_at' => ['nullable', 'date'],
            'order' => ['integer', 'min:0'],
            'featuredImage' => ['nullable', 'image', 'max:2048'],
        ];
    }

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================
    public function mount(): void
    {
        // Check authorization
        $this->authorize('your-models.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    // Optional: Auto-generate slug from title
    public function updatedTitle(): void
    {
        if (!$this->isEditing || empty($this->slug)) {
            $this->slug = \Str::slug($this->title);
        }
    }

    // ==========================================
    // TABLE ACTIONS
    // ==========================================
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ==========================================
    // CRUD ACTIONS
    // ==========================================
    public function create(): void
    {
        $this->authorize('your-models.create');
        $this->resetForm();
        $this->showForm = true;
        $this->isEditing = false;
    }

    public function edit(int $id): void
    {
        $this->authorize('your-models.edit');
        
        $model = YourModel::findOrFail($id);
        
        // Load model data into form
        $this->selectedId = $model->id;
        $this->title = $model->title;
        $this->slug = $model->slug;
        $this->description = $model->description;
        $this->status = $model->status;
        $this->published_at = $model->published_at?->format('Y-m-d');
        $this->order = $model->order;
        
        $this->showForm = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->authorize($this->isEditing ? 'your-models.edit' : 'your-models.create');
        
        $validated = $this->validate();
        
        if ($this->isEditing) {
            // Update existing model
            $model = YourModel::findOrFail($this->selectedId);
            $model->update($validated);
            
            // Optional: Handle file upload
            if ($this->featuredImage) {
                $model->addMedia($this->featuredImage->getRealPath())
                    ->toMediaCollection('featured_image');
            }
            
            $this->success('Record updated successfully!');
        } else {
            // Create new model
            $model = YourModel::create($validated);
            
            // Optional: Handle file upload
            if ($this->featuredImage) {
                $model->addMedia($this->featuredImage->getRealPath())
                    ->toMediaCollection('featured_image');
            }
            
            $this->success('Record created successfully!');
        }
        
        $this->resetForm();
        $this->showForm = false;
        
        // Activity is automatically logged by GlobalActivityObserver!
    }

    public function confirmDelete(int $id): void
    {
        $this->authorize('your-models.delete');
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        $this->authorize('your-models.delete');
        
        if ($this->confirmingDeleteId) {
            YourModel::findOrFail($this->confirmingDeleteId)->delete();
            $this->success('Record deleted successfully!');
            $this->confirmingDeleteId = null;
            
            // Activity is automatically logged by GlobalActivityObserver!
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================
    protected function resetForm(): void
    {
        $this->selectedId = null;
        $this->title = '';
        $this->slug = '';
        $this->description = '';
        $this->status = 'draft';
        $this->published_at = null;
        $this->order = 0;
        $this->featuredImage = null;
        $this->resetValidation();
    }

    // ==========================================
    // RENDER
    // ==========================================
    public function render()
    {
        $query = YourModel::query();

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhere('slug', 'like', "%{$this->search}%");
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        // Paginate
        $models = $query->paginate($this->perPage);

        return view('livewire.app.your-model-component', [
            'models' => $models,
        ]);
    }
}
```

---

## Step 4: Build the UI

## Step 4: Build the UI

### Blade View Template

Create `resources/views/livewire/app/your-model-component.blade.php`:

```blade
<div>
    {{-- ========================================== --}}
    {{-- HEADER --}}
    {{-- ========================================== --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold dark:text-white">Your Models</h1>
        @can('your-models.create')
            <x-button wire:click="create" primary>
                <x-icon name="plus" class="w-5 h-5 mr-2" />
                New Record
            </x-button>
        @endcan
    </div>

    {{-- ========================================== --}}
    {{-- FILTERS & SEARCH --}}
    {{-- ========================================== --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        {{-- Search --}}
        <x-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search..." 
            icon="magnifying-glass"
        />
        
        {{-- Status Filter --}}
        <x-select 
            wire:model.live="statusFilter" 
            placeholder="All Statuses"
        >
            <x-option value="">All Statuses</x-option>
            <x-option value="draft">Draft</x-option>
            <x-option value="published">Published</x-option>
            <x-option value="active">Active</x-option>
            <x-option value="inactive">Inactive</x-option>
        </x-select>
        
        {{-- Per Page --}}
        <x-select wire:model.live="perPage">
            <x-option value="10">10 per page</x-option>
            <x-option value="25">25 per page</x-option>
            <x-option value="50">50 per page</x-option>
            <x-option value="100">100 per page</x-option>
        </x-select>
    </div>

    {{-- ========================================== --}}
    {{-- DATA TABLE --}}
    {{-- ========================================== --}}
    <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    {{-- Sortable columns --}}
                    <th 
                        wire:click="sortBy('title')" 
                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600"
                    >
                        <div class="flex items-center gap-2">
                            Title
                            @if($sortField === 'title')
                                <x-icon 
                                    name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" 
                                    class="w-4 h-4"
                                />
                            @endif
                        </div>
                    </th>
                    
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                        Status
                    </th>
                    
                    <th 
                        wire:click="sortBy('created_at')" 
                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600"
                    >
                        <div class="flex items-center gap-2">
                            Created
                            @if($sortField === 'created_at')
                                <x-icon 
                                    name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" 
                                    class="w-4 h-4"
                                />
                            @endif
                        </div>
                    </th>
                    
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-300">
                        Actions
                    </th>
                </tr>
            </thead>
            
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($models as $model)
                    <tr wire:key="model-{{ $model->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $model->title }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ Str::limit($model->description, 50) }}
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge 
                                :color="match($model->status) {
                                    'published', 'active' => 'success',
                                    'draft' => 'warning',
                                    'inactive' => 'error',
                                    default => 'info'
                                }"
                            >
                                {{ ucfirst($model->status) }}
                            </x-badge>
                        </td>
                        
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                            {{ $model->created_at->format('M d, Y') }}
                        </td>
                        
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex justify-end gap-2">
                                @can('your-models.edit')
                                    <x-button 
                                        wire:click="edit({{ $model->id }})" 
                                        wire:loading.attr="disabled"
                                        sm
                                        flat
                                    >
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </x-button>
                                @endcan
                                
                                @can('your-models.delete')
                                    <x-button 
                                        wire:click="confirmDelete({{ $model->id }})" 
                                        wire:loading.attr="disabled"
                                        color="error" 
                                        sm
                                        flat
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </x-button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <x-icon name="inbox" class="w-12 h-12 mx-auto mb-4 text-gray-400" />
                            <p class="text-lg font-medium">No records found</p>
                            <p class="text-sm">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pagination --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700">
            {{ $models->links() }}
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- CREATE/EDIT FORM MODAL --}}
    {{-- ========================================== --}}
    @if($showForm)
        <x-modal wire:model="showForm" title="{{ $isEditing ? 'Edit Record' : 'Create New Record' }}">
            <form wire:submit="save" class="space-y-4">
                {{-- Title --}}
                <x-input 
                    wire:model.live.debounce="title" 
                    label="Title" 
                    placeholder="Enter title"
                    required 
                />
                
                {{-- Slug --}}
                <x-input 
                    wire:model="slug" 
                    label="Slug" 
                    placeholder="auto-generated-slug"
                    hint="Leave empty to auto-generate from title"
                />
                
                {{-- Description --}}
                <x-textarea 
                    wire:model="description" 
                    label="Description" 
                    placeholder="Enter description"
                    rows="4"
                    required 
                />
                
                {{-- Status --}}
                <x-select 
                    wire:model="status" 
                    label="Status"
                    required
                >
                    <x-option value="draft">Draft</x-option>
                    <x-option value="published">Published</x-option>
                    <x-option value="active">Active</x-option>
                    <x-option value="inactive">Inactive</x-option>
                </x-select>
                
                {{-- Published At --}}
                <x-input 
                    wire:model="published_at" 
                    type="date" 
                    label="Publish Date" 
                />
                
                {{-- Order --}}
                <x-input 
                    wire:model="order" 
                    type="number" 
                    label="Display Order" 
                    min="0"
                />
                
                {{-- Optional: File Upload --}}
                <div>
                    <x-file 
                        wire:model="featuredImage" 
                        label="Featured Image" 
                        accept="image/*"
                    />
                    
                    {{-- Loading indicator --}}
                    <div wire:loading wire:target="featuredImage" class="mt-2 text-sm text-gray-500">
                        Uploading...
                    </div>
                    
                    {{-- Preview --}}
                    @if ($featuredImage)
                        <div class="mt-2">
                            <img src="{{ $featuredImage->temporaryUrl() }}" class="h-20 rounded">
                        </div>
                    @endif
                </div>
                
                {{-- Form Actions --}}
                <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-600">
                    <x-button 
                        type="button" 
                        wire:click="cancel"
                        wire:loading.attr="disabled"
                    >
                        Cancel
                    </x-button>
                    
                    <x-button 
                        type="submit" 
                        primary
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="save">
                            {{ $isEditing ? 'Update' : 'Create' }}
                        </span>
                        <span wire:loading wire:target="save">
                            Saving...
                        </span>
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endif

    {{-- ========================================== --}}
    {{-- DELETE CONFIRMATION MODAL --}}
    {{-- ========================================== --}}
    @if($confirmingDeleteId)
        <x-modal wire:model="confirmingDeleteId" title="Confirm Deletion">
            <div class="space-y-4">
                <p class="text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this record? This action cannot be undone.
                </p>
                
                <div class="flex justify-end gap-2">
                    <x-button 
                        wire:click="cancelDelete"
                        wire:loading.attr="disabled"
                    >
                        Cancel
                    </x-button>
                    
                    <x-button 
                        wire:click="delete" 
                        color="error"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="delete">Delete</span>
                        <span wire:loading wire:target="delete">Deleting...</span>
                    </x-button>
                </div>
            </div>
        </x-modal>
    @endif
</div>
```

---

## Step 5: Testing

## Step 5: Testing

### Create Feature Tests

Generate a test file:

```bash
php artisan make:test YourModelComponentTest --no-interaction
```

### Test Template

Create `tests/Feature/YourModelComponentTest.php`:

```php
<?php

use App\Models\User;
use App\Models\YourModel;
use Livewire\Livewire;
use App\Livewire\App\YourModelComponent;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the component', function () {
    Livewire::test(YourModelComponent::class)
        ->assertStatus(200);
});

it('can create a new record', function () {
    Livewire::test(YourModelComponent::class)
        ->call('create')
        ->assertSet('showForm', true)
        ->assertSet('isEditing', false)
        ->set('title', 'Test Record')
        ->set('description', 'This is a test description')
        ->set('status', 'draft')
        ->call('save')
        ->assertHasNoErrors();

    expect(YourModel::where('title', 'Test Record')->exists())->toBeTrue();
});

it('can update an existing record', function () {
    $model = YourModel::factory()->create(['title' => 'Original Title']);

    Livewire::test(YourModelComponent::class)
        ->call('edit', $model->id)
        ->assertSet('selectedId', $model->id)
        ->assertSet('title', 'Original Title')
        ->assertSet('isEditing', true)
        ->set('title', 'Updated Title')
        ->call('save')
        ->assertHasNoErrors();

    expect($model->fresh()->title)->toBe('Updated Title');
});

it('can delete a record', function () {
    $model = YourModel::factory()->create();

    Livewire::test(YourModelComponent::class)
        ->call('confirmDelete', $model->id)
        ->assertSet('confirmingDeleteId', $model->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(YourModel::find($model->id))->toBeNull();
});

it('automatically logs activity when record is created', function () {
    $model = YourModel::create([
        'title' => 'Test Record',
        'description' => 'Description',
        'status' => 'draft',
    ]);

    // ✅ Activity is automatically logged by GlobalActivityObserver
    $this->assertDatabaseHas('activities', [
        'subject_type' => YourModel::class,
        'subject_id' => $model->id,
        'event' => 'created',
    ]);
});

it('automatically logs activity when record is updated', function () {
    $model = YourModel::factory()->create(['status' => 'draft']);
    
    $model->update(['status' => 'published']);

    // ✅ Activity is automatically logged by GlobalActivityObserver
    $this->assertDatabaseHas('activities', [
        'subject_type' => YourModel::class,
        'subject_id' => $model->id,
        'event' => 'updated',
    ]);
});

it('automatically logs activity when record is deleted', function () {
    $model = YourModel::factory()->create();
    $modelId = $model->id;
    
    $model->delete();

    // ✅ Activity is automatically logged by GlobalActivityObserver
    $this->assertDatabaseHas('activities', [
        'subject_type' => YourModel::class,
        'subject_id' => $modelId,
        'event' => 'deleted',
    ]);
});

it('filters records by search term', function () {
    YourModel::factory()->create(['title' => 'Laravel Tutorial']);
    YourModel::factory()->create(['title' => 'Vue.js Guide']);

    Livewire::test(YourModelComponent::class)
        ->set('search', 'Laravel')
        ->assertSee('Laravel Tutorial')
        ->assertDontSee('Vue.js Guide');
});

it('filters records by status', function () {
    YourModel::factory()->create(['status' => 'draft', 'title' => 'Draft Record']);
    YourModel::factory()->create(['status' => 'published', 'title' => 'Published Record']);

    Livewire::test(YourModelComponent::class)
        ->set('statusFilter', 'draft')
        ->assertSee('Draft Record')
        ->assertDontSee('Published Record');
});

it('can sort by column', function () {
    YourModel::factory()->create(['title' => 'B Record']);
    YourModel::factory()->create(['title' => 'A Record']);

    Livewire::test(YourModelComponent::class)
        ->call('sortBy', 'title')
        ->assertSet('sortField', 'title')
        ->assertSet('sortDirection', 'asc');
});

it('validates required fields', function () {
    Livewire::test(YourModelComponent::class)
        ->call('create')
        ->set('title', '')
        ->set('description', '')
        ->call('save')
        ->assertHasErrors(['title', 'description']);
});

it('resets form when cancelled', function () {
    Livewire::test(YourModelComponent::class)
        ->call('create')
        ->set('title', 'Test')
        ->set('description', 'Test Description')
        ->call('cancel')
        ->assertSet('showForm', false)
        ->assertSet('title', '')
        ->assertSet('description', '');
});
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/YourModelComponentTest.php

# Run tests with filter
php artisan test --filter=YourModel

# Run with coverage (if needed)
php artisan test --coverage
```

---

## Best Practices

## Best Practices

### 1. **Use Lifecycle Hooks for Reactive Behavior**

```php
public function updatedTitle(): void
{
    // Auto-generate slug when title changes
    if (!$this->isEditing || empty($this->slug)) {
        $this->slug = Str::slug($this->title);
    }
}

public function updatedSearch(): void
{
    // Reset pagination when search changes
    $this->resetPage();
}
```

### 2. **Add Loading States for Better UX**

```blade
<x-button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove wire:target="save">Save Record</span>
    <span wire:loading wire:target="save">
        <x-icon name="arrow-path" class="w-4 h-4 animate-spin" />
        Saving...
    </span>
</x-button>
```

### 3. **Always Use wire:key in Loops**

```blade
@foreach($models as $model)
    <tr wire:key="model-{{ $model->id }}">
        {{-- This prevents Livewire DOM diffing issues --}}
    </tr>
@endforeach
```

### 4. **Validate Early and Often**

```php
public function updatedTitle(): void
{
    $this->validateOnly('title');
}

public function updatedEmail(): void
{
    $this->validateOnly('email');
}
```

### 5. **Use Form Requests for Complex Validation**

```php
// Create app/Http/Requests/YourModelRequest.php
php artisan make:request YourModelRequest --no-interaction

// Then in your component:
public function save(): void
{
    $validated = $this->validate((new YourModelRequest())->rules());
    // ...
}
```

### 6. **Don't Manually Log Activities**

❌ **Don't do this:**
```php
public function save(): void
{
    $model = YourModel::create($validated);
    
    // DON'T manually log activities!
    Activity::create([...]);
}
```

✅ **Do this:**
```php
public function save(): void
{
    $model = YourModel::create($validated);
    
    // That's it! GlobalActivityObserver handles logging automatically
}
```

### 7. **Always Use Authorization**

```php
public function mount(): void
{
    $this->authorize('your-models.view');
}

public function create(): void
{
    $this->authorize('your-models.create');
}

public function edit(int $id): void
{
    $this->authorize('your-models.edit');
}

public function delete(): void
{
    $this->authorize('your-models.delete');
}
```

### 8. **Handle Media Properly with Spatie Media Library**

```php
// Upload media
$model->addMedia($file->getRealPath())
    ->toMediaCollection('featured_image');

// Get media URL with conversion
$url = $model->getFirstMediaUrl('featured_image', 'thumb');

// Check if media exists
if ($model->hasMedia('featured_image')) {
    // ...
}

// Clear collection
$model->clearMediaCollection('featured_image');
```

### 9. **Use Debouncing for Search Inputs**

```blade
{{-- Debounce to reduce server requests --}}
<x-input 
    wire:model.live.debounce.300ms="search" 
    placeholder="Search..." 
/>
```

### 10. **Follow Laravel Conventions**

- Use snake_case for database columns
- Use camelCase for PHP properties and methods
- Use PascalCase for class names
- Keep components focused (single responsibility)
- Extract complex logic to service classes

---

## Example: Page Component

Here's the **real Page component** from this application as a reference:

### Page Model
Location: `app/Models/Page.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'status',
        'published_at',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->quality(75)
            ->nonQueued();
    }

    public function getImageUrlAttribute()
    {
        $media = $this->getFirstMedia('featured_image');
        if ($media) {
            $path = $media->getPath('thumb');
            if (file_exists($path)) {
                return $media->getUrl('thumb');
            }
        }
        return 'https://placehold.co/400';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               ($this->published_at === null || $this->published_at <= now());
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
```

### Key Features
- ✅ Automatic activity logging (via GlobalActivityObserver)
- ✅ Media library integration for images
- ✅ Auto-slug generation from title
- ✅ SEO fields (meta title, description, keywords)
- ✅ Publishing system with status and scheduling
- ✅ Query scopes for filtering
- ✅ Helper methods for common checks

---

## Troubleshooting

### Activities Not Being Logged

1. ✅ Check if model is in `$excludedModels` array in `GlobalActivityObserver`
2. ✅ Ensure model extends `Illuminate\Database\Eloquent\Model`
3. ✅ Verify `AppServiceProvider::boot()` registers the global observer
4. ✅ Check if `activities` table exists in database

### Slug Not Auto-Generating

1. ✅ Ensure slug field is nullable in migration
2. ✅ Check boot() method is not overridden incorrectly
3. ✅ Verify `Str::slug()` is imported

### Media Not Uploading

1. ✅ Ensure `WithFileUploads` trait is used in component
2. ✅ Check `config/filesystems.php` configuration
3. ✅ Run `php artisan storage:link`
4. ✅ Verify `storage/` directory permissions (775)
5. ✅ Check model implements `HasMedia` interface

### Livewire Component Not Rendering

1. ✅ Ensure component is in correct namespace
2. ✅ Check view file exists at correct path
3. ✅ Run `php artisan livewire:discover`
4. ✅ Clear cache: `php artisan optimize:clear`

### Validation Not Working

1. ✅ Check `rules()` method returns array
2. ✅ Ensure field names match component properties
3. ✅ Use `$this->validate()` or `$this->validateOnly()`
4. ✅ Display errors in blade: `@error('field') ... @enderror`

---

## Quick Reference

### Common Artisan Commands

```bash
# Create model with everything
php artisan make:model YourModel -mfsc --no-interaction

# Create Livewire component
php artisan make:livewire App\\YourComponent --no-interaction

# Create factory
php artisan make:factory YourModelFactory --no-interaction

# Create seeder
php artisan make:seeder YourModelSeeder --no-interaction

# Create test
php artisan make:test YourModelTest --no-interaction

# Run tests
php artisan test --filter=YourModel

# Format code
vendor/bin/pint --dirty

# Clear cache
php artisan optimize:clear
```

### Code Formatting

Always run Pint before committing:

```bash
vendor/bin/pint --dirty
```

---

## Additional Resources

- [Livewire v4 Documentation](https://livewire.laravel.com)
- [Laravel 12 Documentation](https://laravel.com/docs)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)
- [Global Activity Logging Guide](./docs/GLOBAL_ACTIVITY_LOGGING.md)
- [Pest v4 Testing](https://pestphp.com/docs)
- [Mary UI Components](https://mary-ui.com)

---

## Summary

✅ **Step 1**: Create model with migrations, factories, seeders  
✅ **Step 2**: Activity logging happens automatically (no config needed!)  
✅ **Step 3**: Create Livewire component with Artisan  
✅ **Step 4**: Build UI with table, filters, and forms  
✅ **Step 5**: Write comprehensive feature tests  
✅ **Best Practices**: Follow conventions and use proper authorization  

**You're ready to build features quickly and confidently!**

Use this template as your starting point for any new CRUD feature. Just replace `YourModel` with your actual model name and customize the fields to match your requirements.

Happy coding! 🚀

