# Development Guide - Quick Start

> **A simple guide for building features in this Laravel application.**

---

## 🚀 Creating a New Feature (CRUD)

### 1. Create the Model

```bash
php artisan make:model Post -mfs --no-interaction
```

This creates: Model + Migration + Factory + Seeder

### 2. Define Your Migration

```php
// database/migrations/xxxx_create_posts_table.php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique()->nullable();
    $table->text('content');
    $table->string('status')->default('draft'); // draft, published
    $table->timestamps();
});
```

### 3. Set Up Your Model

```php
// app/Models/Post.php
protected $fillable = ['title', 'slug', 'content', 'status'];

protected function casts(): array
{
    return [
        'created_at' => 'datetime',
    ];
}

// Optional: Auto-generate slug
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        if (empty($model->slug)) {
            $model->slug = \Str::slug($model->title);
        }
    });
}
```

### 4. Create Livewire Component

```bash
php artisan make:livewire App\\Post --no-interaction
```

### 5. Implement CRUD in Component

```php
// app/Livewire/App/Post.php
use Livewire\Component;
use Livewire\WithPagination;

class Post extends Component
{
    use WithPagination;

    // Table
    public string $search = '';
    
    // Form
    public ?int $selectedId = null;
    public string $title = '';
    public string $content = '';
    public bool $showForm = false;

    public function create()
    {
        $this->reset(['selectedId', 'title', 'content']);
        $this->showForm = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        if ($this->selectedId) {
            Post::find($this->selectedId)->update($validated);
        } else {
            Post::create($validated);
        }

        $this->showForm = false;
        $this->reset();
    }

    public function render()
    {
        return view('livewire.app.post', [
            'posts' => Post::where('title', 'like', "%{$this->search}%")
                ->latest()
                ->paginate(10),
        ]);
    }
}
```

### 6. Build the View

```blade
{{-- resources/views/livewire/app/post.blade.php --}}
<div>
    <div class="flex justify-between mb-4">
        <x-input wire:model.live="search" placeholder="Search..." />
        <x-button wire:click="create">New Post</x-button>
    </div>

    <table>
        @foreach($posts as $post)
            <tr wire:key="post-{{ $post->id }}">
                <td>{{ $post->title }}</td>
                <td>{{ $post->created_at->format('M d, Y') }}</td>
            </tr>
        @endforeach
    </table>

    {{ $posts->links() }}
</div>
```

### 7. Add Route

```php
// routes/web.php
Route::get('/posts', App\Livewire\App\Post::class)->name('posts');
```

---

## ✅ That's It!

**Your model is automatically logged!** Every create, update, and delete operation is tracked by the `GlobalActivityObserver` - no extra code needed.

---

## 📝 Essential Commands

```bash
# Create model with everything
php artisan make:model Product -mfsc --no-interaction

# Create Livewire component
php artisan make:livewire App\\YourComponent --no-interaction

# Run migrations
php artisan migrate

# Run tests
php artisan test

# Format code
vendor/bin/pint --dirty

# Clear cache
php artisan optimize:clear
```

---

## 🧪 Writing Tests

```php
// tests/Feature/PostTest.php
it('can create a post', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'Content here',
    ]);

    expect($post->title)->toBe('Test Post');
    
    // Activity is automatically logged!
    $this->assertDatabaseHas('activities', [
        'subject_type' => Post::class,
        'subject_id' => $post->id,
        'event' => 'created',
    ]);
});
```

---

## 🎯 Key Conventions

1. **Models** → `app/Models/` → PascalCase → `Post.php`
2. **Migrations** → `database/migrations/` → snake_case → `create_posts_table.php`
3. **Livewire** → `app/Livewire/App/` → PascalCase → `Post.php`
4. **Views** → `resources/views/livewire/app/` → kebab-case → `post.blade.php`
5. **Routes** → Use named routes → `route('posts')`

---

## 🔒 Authorization

Always check permissions in your components:

```php
public function mount(): void
{
    $this->authorize('posts.view');
}

public function create(): void
{
    $this->authorize('posts.create');
}
```

---

## �� Full Documentation

For detailed templates and advanced features, see:
- **LIVEWIRE_INSTRUCTIONS.md** - Complete feature development template
- **docs/GLOBAL_ACTIVITY_LOGGING.md** - Activity logging details
- **docs/** - Other feature-specific guides

---

## 💡 Quick Tips

✅ **Always use wire:key** in loops → `wire:key="item-{{ $item->id }}"`  
✅ **Debounce search inputs** → `wire:model.live.debounce.300ms="search"`  
✅ **Add loading states** → `wire:loading` for better UX  
✅ **Use factories in tests** → `Post::factory()->create()`  
✅ **Run Pint before commit** → `vendor/bin/pint --dirty`  

---

## 🆘 Need Help?

1. Check **LIVEWIRE_INSTRUCTIONS.md** for complete templates
2. Look at existing components in `app/Livewire/App/` for examples
3. Search docs: Laravel, Livewire, Pest, Mary UI

---

**Happy Coding! 🚀**

