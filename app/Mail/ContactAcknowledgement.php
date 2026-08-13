<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the visitor, not the site owner.
 *
 * Queued, unlike the owner notification: this goes to an address supplied by
 * whoever filled the form, so a slow or refusing mail server must not hold the
 * visitor's request open.
 *
 * The submitted message is deliberately never quoted back. This mailable is
 * reachable by anyone who can pass the contact form's bot checks, and echoing
 * attacker-supplied text to an arbitrary address would make the form useful
 * for delivering content to third parties.
 *
 * Language comes from Mail::to(...)->locale(...) at the call site, which wraps
 * rendering in withLocale, so both the subject and body follow the locale the
 * form was submitted in.
 */
class ContactAcknowledgement extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public function __construct(
        public ContactSubmission $submission,
        public string $ownerEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('site.contact_ack_subject'),
            replyTo: [$this->ownerEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-acknowledgement',
            with: [
                'name' => $this->submission->name,
                'ownerEmail' => $this->ownerEmail,
            ],
        );
    }
}
