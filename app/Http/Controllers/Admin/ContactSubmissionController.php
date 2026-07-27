<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContactSubmissionController extends Controller
{
    public function index(): InertiaResponse
    {
        $submissions = ContactSubmission::latest()->paginate(15)
            ->through(fn (ContactSubmission $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'email' => $s->email,
                'company' => $s->company,
                'budget' => $s->budget,
                'message' => $s->message,
                'is_read' => $s->read_at !== null,
                'when' => $s->created_at?->diffForHumans(),
            ]);

        return Inertia::render('ContactSubmissions/Index', [
            'submissions' => $submissions,
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

    public function destroy(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->delete();

        return back()->with('status', 'Message deleted.');
    }
}
