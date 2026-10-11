<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof \App\Models\User) {
            return;
        }

        Mail::to($user->email)->send(new WelcomeMail($user));
    }
}
