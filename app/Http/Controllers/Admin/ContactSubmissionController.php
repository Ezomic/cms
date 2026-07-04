<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;

class ContactSubmissionController extends Controller
{
    public function index()
    {
        return view('admin.contact-submissions.index', [
            'submissions' => ContactSubmission::latest()->paginate(15),
        ]);
    }

    public function markRead(ContactSubmission $contactSubmission)
    {
        $contactSubmission->forceFill(['read_at' => now()])->save();

        return back();
    }

    public function markUnread(ContactSubmission $contactSubmission)
    {
        $contactSubmission->forceFill(['read_at' => null])->save();

        return back();
    }

    public function destroy(ContactSubmission $contactSubmission)
    {
        $contactSubmission->delete();

        return back()->with('status', 'Message deleted.');
    }
}
