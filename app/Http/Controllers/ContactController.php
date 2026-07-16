<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use App\Models\ContactSubmission;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('status', __('site.contact_success'));
        }

        $submission = ContactSubmission::create($data);

        $profileEmail = Profile::current()->email;
        if ($profileEmail) {
            // Sent synchronously so the notification never depends on a queue
            // worker. The submission is already saved to the admin inbox, so a
            // mail failure is reported but must not break the visitor's success
            // response.
            try {
                Mail::to($profileEmail)->send(new ContactFormSubmitted($submission));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('status', __('site.contact_success'));
    }
}
