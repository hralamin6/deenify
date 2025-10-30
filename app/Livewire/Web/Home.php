<?php

namespace App\Livewire\Web;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.web.home')->layout('layouts.auth');
    }
}
