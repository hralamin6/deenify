<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::livewire('/', 'web::home')->name('web.home');
Route::livewire('/campaigns', 'web::campaigns')->name('web.campaigns');
Route::livewire('/campaigns/{slug}', 'web::campaign')->name('web.campaign');
Route::livewire('/contributions', 'web::contributions')->name('web.contributions');
Route::livewire('/contributions/{slug}', 'web::contribution')->name('web.contribution');

Route::middleware('auth')->group(function () {
    Route::livewire('/app/', 'app::dashboard')->name('app.dashboard');

    Route::livewire('/app/profile/', 'app::profile')->name('app.profile');
    Route::livewire('/app/settings/', 'app::settings')->name('app.settings');
    Route::livewire('/app/roles/', 'app::roles')->name('app.roles');
    Route::livewire('/app/users/', 'app::users')->name('app.users');
    Route::livewire('/app/backups/', 'app::backups')->name('app.backups');
    Route::livewire('/app/translate/', 'app::translate')->name('app.translate');
    Route::livewire('/app/pages/', 'app::pages')->name('app.pages');
    Route::livewire('/app/campaigns/', 'app::campaigns')->name('app.campaigns');
    Route::livewire('/app/expense-categories/', 'app::expense-categories')->name('app.expense-categories');
    Route::livewire('/app/expenses/', 'app::expenses')->name('app.expenses');
    Route::livewire('/app/donations/', 'app::donations')->name('app.donations');
    Route::livewire('/app/recurring-plans/', 'app::recurring-plans')->name('app.recurring-plans');
    Route::livewire('/app/payment-attempts/', 'app::payment-attempts')->name('app.payment-attempts');
    Route::livewire('/app/contributions/', 'app::contributions')->name('app.contributions');

    Route::livewire('/app/notifications/', 'app::notifications')->name('app.notifications');

    Route::livewire('/app/activities/feed/', 'app::activity-feed')->name('app.activity.feed');
    Route::livewire('/app/activities/my/', 'app::my-activities')->name('app.activity.my');

    // Chat routes
    Route::livewire('/app/chat/{conversation?}', 'app::chat')->name('app.chat');
    Route::livewire('/app/ai-chat/{conversation?}', 'app::ai-chat')->name('app.ai-chat');
});

// Push notification API routes (now accessible to both guests and authenticated users)
Route::post('api/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('api/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
Route::get('api/push/status', [\App\Http\Controllers\PushSubscriptionController::class, 'status'])->name('push.status');

// Public VAPID key endpoint (must be accessible without authentication)
Route::get('api/push/vapid-key', [\App\Http\Controllers\PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid-key');

Route::livewire('{slug}', 'web::page')->name('web.page');

// Payment routes
Route::get('payment/shurjopay/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.shurjopay.callback');
Route::get('payment/shurjopay/cancel', [\App\Http\Controllers\PaymentController::class, 'cancel'])->name('payment.shurjopay.cancel');

Route::get('payment/aamarpay/redirect', [\App\Http\Controllers\PaymentController::class, 'aamarpayRedirect'])->name('payment.aamarpay.redirect');
Route::post('payment/aamarpay/callback', [\App\Http\Controllers\PaymentController::class, 'aamarpayCallback'])->name('payment.aamarpay.callback');
Route::post('payment/aamarpay/cancel', [\App\Http\Controllers\PaymentController::class, 'aamarpayCancel'])->name('payment.aamarpay.cancel');
