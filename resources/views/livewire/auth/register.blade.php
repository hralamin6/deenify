@section('title', 'Create a new account')

<div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a wire:navigate href="{{ route('web.home') }}">
            <x-logo class="w-auto h-16 mx-auto text-indigo-600" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900 leading-9">
            Create a new account
        </h2>

        <p class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            Or
            <a wire:navigate href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                sign in to your account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card>
            <form wire:submit.prevent="register" class="space-y-6">
                <x-input label="Name" id="name" wire:model.lazy="name" required autofocus />
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <x-input label="Email Address"
                         wire:model.lazy="email"
                         type="email"
                         required
                         icon="o-envelope"
                         placeholder="Enter your email"/>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <x-password label="Password" id="password" wire:model.lazy="password" required />
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <x-password label="Confirm Password" id="password_confirmation" wire:model.lazy="passwordConfirmation" required />

                <x-button type="submit" class="w-full">Register</x-button>
            </form>
        </x-card>
    </div>
</div>
