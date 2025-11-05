<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\LogsActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPushSubscriptions, HasRoles, InteractsWithMedia, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')->singleFile()->registerMediaConversions(function (?Media $media = null) {
            $this->addMediaConversion('thumb')->quality('10')->nonQueued();
        });

        $this->addMediaCollection('banner')->singleFile()->registerMediaConversions(function (?Media $media = null) {
            $this->addMediaConversion('thumb')->quality('10')->nonQueued();
        });
    }

    public function getAvatarUrlAttribute()
    {
        $media = $this->getFirstMedia('profile');

        // ✅ Step 1: Check if media exists and file is available
        if ($media) {
            $path = $media->getPath('thumb');

            // check if file exists in server
            if (file_exists($path)) {
                return $media->getUrl('thumb');
            }
        }

        // ✅ Step 2: Fallback to external avatar generator
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=random';
    }

    public function getBannerUrlAttribute()
    {
        $media = $this->getFirstMedia('banner');

        // Check if media exists and file is available
        if ($media) {
            $path = $media->getPath();

            if (file_exists($path)) {
                return $media->getUrl();
            }
        }

        // Fallback to a default banner image
        return null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences()
    {
        return $this->hasMany(\App\Models\NotificationPreference::class);
    }

    /**
     * Get notification preference for a specific category.
     */
    public function getNotificationPreference(string $category = 'general')
    {
        return $this->notificationPreferences()
            ->where('category', $category)
            ->first() ?? $this->notificationPreferences()->create([
                'category' => $category,
            ]);
    }

    /**
     * Check if push notifications are enabled for a category.
     */
    public function isPushEnabledFor(string $category = 'general'): bool
    {
        return $this->getNotificationPreference($category)->push_enabled ?? true;
    }

    /**
     * Get all activities caused by this user.
     */
    public function activities()
    {
        return $this->morphMany(\App\Models\Activity::class, 'causer')->orderBy('created_at', 'desc');
    }

    /**
     * Get activities where this user is the subject.
     */
    public function subjectActivities()
    {
        return $this->morphMany(\App\Models\Activity::class, 'subject')->orderBy('created_at', 'desc');
    }

    /**
     * Get the user's details.
     */
    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }
}
