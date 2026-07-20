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

        // Bot signals: a hidden field real visitors never fill, and a budget the
        // form's <select> cannot emit. Both get the success response so a
        // spammer learns nothing about why the message was dropped.
        if ($request->filled('website') || ! $this->budgetIsFromForm($data['budget'] ?? null)) {
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

    /**
     * The budget field is a <select> with a fixed option set, so any other value
     * was not produced by our form (scrapers post the raw HTML value back
     * without decoding entities, e.g. "&gt; €50k"). Falls open when the options
     * cannot be resolved: dropping a real enquiry costs more than storing spam.
     */
    private function budgetIsFromForm(?string $budget): bool
    {
        $options = __('site.contact_budget_options');

        if (! is_array($options)) {
            return true;
        }

        return in_array((string) $budget, array_keys($options), true);
    }
}
