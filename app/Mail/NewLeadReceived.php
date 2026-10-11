<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent to the team for every public contact-form submission, so a lead never
 * sits unseen in /admin/leads. Reply-To is the visitor, so hitting "Reply"
 * in the mail client answers them directly.
 */
class NewLeadReceived extends Mailable
{
    public function __construct(public Lead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New enquiry from '.$this->lead->name.($this->lead->company ? ' ('.$this->lead->company.')' : ''),
            replyTo: [new Address($this->lead->email, $this->lead->name)],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.new-lead');
    }
}
