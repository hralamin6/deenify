<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Setting extends Model implements HasMedia
{
  use InteractsWithMedia;
  protected $fillable = ['key', 'value'];
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('logo')
      ->useDisk('public')
      ->singleFile()
      ->registerMediaConversions(function (Media $media = null) {
        $this->addMediaConversion('thumb')
          ->width(150)
          ->height(150)
          ->nonQueued();
      });
      
    $this->addMediaCollection('icon')
      ->useDisk('public')
      ->singleFile()
      ->registerMediaConversions(function (Media $media = null) {
        $this->addMediaConversion('thumb')
          ->width(150)
          ->height(150)
          ->nonQueued();
      });
  }

  public static function get($key, $default = null)
  {
    return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
      return static::query()->where('key', $key)->value('value') ?? $default;
    });
  }

  public static function set($key, $value)
  {
    Cache::forget("setting.{$key}");
    return static::updateOrCreate(['key' => $key], ['value' => $value]);
  }

  public static function clearCache(): void
  {
    Cache::flush();
  }
}
