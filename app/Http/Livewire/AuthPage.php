<?php

namespace App\Http\Livewire;

use Livewire\Component;

class AuthPage extends Component
{


    public string $activeTab = 'signin'; // or 'register'


    public function render()
    {
        return view('livewire.auth-page');
    }
}
