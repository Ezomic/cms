<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContactSubmissionController extends Controller
{
    /**
     * A lightweight status on an inbox, not a CRM. Outreach pipeline, stages
     * and follow-ups belong in the prospect app; this only answers "did I
     * reply to this, and what came of it".
     */
    private const STATES = ['all', 'unread', 'read', 'replied'];

    public function index(Request $request): InertiaResponse
    {
        $state = $request->string('state')->toString();

        if (! in_array($state, self::STATES, true)) {
            $state = 'all';
        }

        $submissions = ContactSubmission::latest()
            ->when($state === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($state === 'read', fn ($query) => $query->whereNotNull('read_at')->whereNull('replied_at'))
            ->when($state === 'replied', fn ($query) => $query->whereNotNull('replied_at'))
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ContactSubmission $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'email' => $s->email,
                'company' => $s->company,
                'budget' => $s->budget,
                'message' => $s->message,
                'is_read' => $s->read_at !== null,
                'is_replied' => $s->replied_at !== null,
                'replied_when' => $s->replied_at?->diffForHumans(),
                'note' => $s->note,
                'when' => $s->created_at?->diffForHumans(),
            ]);

        return Inertia::render('ContactSubmissions/Index', [
            'submissions' => $submissions,
            'filters' => ['state' => $state],
            'counts' => [
                'all' => ContactSubmission::count(),
                'unread' => ContactSubmission::whereNull('read_at')->count(),
                'read' => ContactSubmission::whereNotNull('read_at')->whereNull('replied_at')->count(),
                'replied' => ContactSubmission::whereNotNull('replied_at')->count(),
            ],
        ]);
    }

    public function markRead(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->forceFill(['read_at' => now()])->save();

        return back();
    }

    public function markUnread(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->forceFill(['read_at' => null])->save();

        return back();
    }

    /**
     * Replying implies having read it, so this also stamps read_at when the
     * message was somehow never opened.
     */
    public function markReplied(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->forceFill([
            'replied_at' => now(),
            'read_at' => $contactSubmission->read_at ?? now(),
        ])->save();

        return back();
    }

    public function markUnreplied(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->forceFill(['replied_at' => null])->save();

        return back();
    }

    public function saveNote(Request $request, ContactSubmission $contactSubmission): RedirectResponse
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = $request->string('note')->trim()->toString();

        $contactSubmission->forceFill(['note' => $note === '' ? null : $note])->save();

        return back()->with('status', 'Note saved.');
    }

    public function destroy(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->delete();

        return back()->with('status', 'Message deleted.');
    }
}
