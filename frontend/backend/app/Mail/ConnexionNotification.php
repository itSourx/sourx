<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConnexionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $ipAddress;
    public $device;
    public $os;
    public $browser;

    /**
     * Create a new message instance.
     */
    public function __construct($ipAddress, $device, $os, $browser)
    {
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->os = $os;
        $this->browser = $browser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle Connexion à votre Compte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.connexion.notification',
            with: [
                'ipAddress' => $this->ipAddress,
                'device' => $this->device,
                'os' => $this->os,
                'browser' => $this->browser,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
