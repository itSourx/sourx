<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemandeRefusee extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $message;

    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject('Votre demande a été refusée')
                    ->view('mail.demande.demandeRefusee') 
                    ->with([
                        'user' => $this->user,
                        'message' => $this->message,
                    ]);
    }
}
