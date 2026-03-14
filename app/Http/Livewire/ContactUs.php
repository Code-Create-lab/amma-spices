<?php

namespace App\Http\Livewire;

use App\Mail\ContactUs as MailContactUs;
use App\Models\ContactUs as ModelsContactUs;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactUs extends Component
{
    public $full_name = '';
    public $email = '';
    public $phone_number = '';
    public $subject = '';
    public $message = '';

    protected $rules = [
        'full_name' => 'required|string|min:3',
        'email' => 'required|email',
        'phone_number' => 'required|digits:10',
        'subject' => 'nullable|string|max:255',
        'message' => 'required|string|min:5',
    ];

    public function save()
    {
        // Validate form data
        $validated = $this->validate();

        // Store in database
        ModelsContactUs::create($validated);

        // Send email
        Mail::to('snehal.yugasa@gmail.com')
            ->bcc('snehal.yugasa@gmail.com')
            ->send(new MailContactUs($validated));

        // Reset form fields
        $this->reset(['full_name', 'email', 'phone_number', 'subject', 'message']);

        // Success message
        session()->flash('message', 'Thank you for your message. It has been sent successfully.');
    }

    public function render()
    {
        return view('livewire.contact-us');
    }
}
