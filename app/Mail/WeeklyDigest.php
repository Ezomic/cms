<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Carries plain figures rather than models, so what the queue worker renders
 * is exactly what the command counted, however long the job waits.
 */
class WeeklyDigest extends Mailable implements ShouldQueue
{
    /**
     * @param  list<array{path: string, views: int}>  $topPaths
     * @param  list<array{host: string, views: int}>  $topReferrers
     */
    public function __construct(
        public string $period,
        public int $views,
        public int $previousViews,
        public array $topPaths,
        public array $topReferrers,
        public int $unreadEnquiries,
    ) {}

    public function isQuiet(): bool
    {
        return $this->views === 0 && $this->unreadEnquiries === 0;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly digest: '.$this->period,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.weekly-digest',
            with: [
                'inboxUrl' => route('admin.contact-submissions.index', ['state' => 'unread']),
            ],
        );
    }
}
