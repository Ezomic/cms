<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use App\Models\ContactSubmission;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('status', 'Thanks — your message has been sent.');
        }

        $submission = ContactSubmission::create($data);

        $profileEmail = Profile::current()->email;
        if ($profileEmail) {
            Mail::to($profileEmail)->send(new ContactFormSubmitted($submission));
        }

        return back()->with('status', 'Thanks — your message has been sent.');
    }
}
