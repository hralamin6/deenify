<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}
if (! function_exists('userImage')) {
    function userImage($model, $collection = 'profile', $conversion = 'thumb')
    {
        $media = $model->getFirstMedia($collection);

        if ($media) {
            $path = $media->getPath($conversion ?: '');

            // check if file exists in server
            if (file_exists($path)) {
                return $media->getUrl($conversion ?: '');
            }
        }

        // fallback if not found or deleted
        return 'https://ui-avatars.com/api/?name='.urlencode($model->name);
    }
}
if (! function_exists('getSettingImage')) {
    function getSettingImage($key = '', $collection = '', $conversion = '', $defaultUrl = 'https://placehold.co/400')
    {
        // Don't use static cache - always fetch fresh to avoid stale data after uploads
        $setting = \App\Models\Setting::where('key', $key)->first();

        if (!$setting) {
            return \setting('placeHolder', $defaultUrl);
        }
        // Return the image URL or the default placeholder
        $url = $conversion 
            ? $setting->getFirstMediaUrl($collection, $conversion)
            : $setting->getFirstMediaUrl($collection);
            
        return $url ?: \setting('placeHolder', $defaultUrl);
    }
}
if (! function_exists('getImage')) {

    function getImage($model, string $collection, ?string $conversion = null, ?string $defaultUrl = null): string
    {
        // 1️⃣ Default placeholder (can be from settings or static)
        $default = $defaultUrl
          ?: setting('placeHolder', 'https://placehold.co/400x300?text=No+Image');

        // 2️⃣ Validate the model and method availability
        if (! $model || ! method_exists($model, 'getFirstMedia')) {
            return $default;
        }

        // 3️⃣ Try to fetch the first media in the given collection
        $media = $model->getFirstMedia($collection);
        if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            return $default;
        }

        // 4️⃣ Determine the filesystem path for this media item
        try {
            $path = $conversion
              ? $media->getPath($conversion)
              : $media->getPath();
        } catch (Exception $e) {
            // Media record might exist but file missing or conversion not generated
            return $default;
        }

        // 5️⃣ Check physical file existence (supports local & cloud)
        if ($path && file_exists($path)) {
            try {
                // Return the full URL of the media (local or remote)
                return $conversion
                  ? $media->getUrl($conversion)
                  : $media->getUrl();
            } catch (Exception $e) {
                // If Spatie throws (e.g., missing disk), fallback
                return $default;
            }
        }

        // 6️⃣ Optional: Try checking on remote disks (S3, etc.)
        try {
            $disk = $media->disk;
            if (Storage::disk($disk)->exists($media->getPathRelativeToRoot())) {
                return $media->getUrl($conversion ?: '');
            }
        } catch (Exception $e) {
            // Silent fail if disk missing
        }

        // 7️⃣ Final guaranteed fallback
        return $default;
    }
}
if (! function_exists('checkImageUrl')) {
    /**
     * Check if a given URL points to a valid image resource.
     *
     * - Validates URL format
     * - Performs HEAD request to verify reachability and content-type
     * - Falls back to GET when HEAD is not supported
     */
    function checkImageUrl(?string $url): bool
    {
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $allowedPrefixes = [
            'image/',              // generic catch-all for images
        ];

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (compatible; ShariatpurCityBot/1.0; +https://example.com/bot)',
            'Accept' => 'image/*,*/*;q=0.8',
        ];

        try {
            $response = Http::timeout(7)->withHeaders($headers)->head($url);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');
                $contentLength = (int) ($response->header('Content-Length') ?? 0);

                if ($contentType && starts_with_any($contentType, $allowedPrefixes) && $contentLength >= 0) {
                    return true;
                }
            }

            // Some servers don't support HEAD; try a lightweight GET
            $response = Http::timeout(10)->withHeaders($headers)->get($url);
            if (! $response->successful()) {
                return false;
            }

            $contentType = $response->header('Content-Type');
            if ($contentType && starts_with_any($contentType, $allowedPrefixes)) {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
