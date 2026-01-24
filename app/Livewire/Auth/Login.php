<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    /** @var string */
    public $email = '';

    /** @var string */
    public $password = '';

    /** @var bool */
    public $remember = false;

    protected $rules = [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ];
    public function quickLogin(string $role): void
    {
        if ($role === 'admin') {
            $this->email = 'admin@mail.com';
            $this->password = '000000';
        } elseif ($role === 'user') {
            $this->email = 'user@mail.com';
            $this->password = '000000';
        }

        $this->authenticate();
    }

    public function authenticate()
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', trans('auth.failed'));

            return;
        }

        session()->regenerate();

        $this->redirect(route('web.home'));
    }

    public function render()
    {
        return view('livewire.auth.login')->extends('layouts.auth');
    }
}
