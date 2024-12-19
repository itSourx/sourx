<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemandeAcceptee extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $message;
    public $attachments;

    public function __construct($user, $message, $attachments)
    {
        $this->user = $user;
        $this->message = $message;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $email = $this->subject('Votre demande a été refusée')
            ->view('mail.demande.demandeRefusee')
            ->with([
                'user' => $this->user,
                'message' => $this->message,
            ]);

        foreach ($this->attachments as $attachment) {
            $email->attach($attachment);
        }

        return $email;
    }
}
