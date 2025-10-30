<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Passwords\Confirm;
use App\Livewire\Auth\Passwords\Email;
use App\Livewire\Auth\Passwords\Reset;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\Verify;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::livewire('/', 'web.home')->name('web.home');
Route::get('/mary', \App\Livewire\Welcome::class);

//Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)
        ->name('login');

    Route::get('register', Register::class)
        ->name('register');

    Route::get('auth/{provider}/redirect', [\App\Http\Controllers\SocialiteController::class, 'loginSocial'])->name('socialite.auth');
    Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialiteController::class, 'callbackSocial'])->name('socialite.callback');

});

Route::get('password/reset', Email::class)
    ->name('password.request');

Route::get('password/reset/{token}', Reset::class)
    ->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('email/verify', Verify::class)
        ->middleware('throttle:6,1')
        ->name('verification.notice');

    Route::get('password/confirm', Confirm::class)
        ->name('password.confirm');
});

Route::middleware(['auth', 'verified'])->group(function () {
  Route::livewire('/app/', 'app.dashboard')->name('app.dashboard');
  Route::livewire('/app/profile/', 'app.profile')->name('app.profile')->middleware('password.confirm');
  Route::livewire('/app/settings/', 'app.setting')->name('app.settings')->middleware('password.confirm');
  Route::livewire('/app/roles/', 'app.role')->name('app.roles')->middleware('password.confirm');
  Route::livewire('/app/users/', 'app.user')->name('app.users')->middleware('password.confirm');
});

Route::middleware('auth')->group(function () {



  Route::get('email/verify/{id}/{hash}', \App\Http\Controllers\Auth\EmailVerificationController::class)
    ->middleware('signed')
    ->name('verification.verify');

  Route::post('logout', \App\Http\Controllers\Auth\LogoutController::class)
    ->name('logout');
});
