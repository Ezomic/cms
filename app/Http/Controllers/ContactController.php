<?php

namespace App\Http\Controllers;

use App\Mail\ContactAcknowledgement;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactSubmission;
use App\Models\Profile;
use App\Support\ContactFormToken;
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

        // Bot signals: a token proving the form was rendered by us and sat on
        // for a plausible amount of time, a hidden field real visitors never
        // fill, and a budget the form's <select> cannot emit. All get the
        // success response so a spammer learns nothing about why the message
        // was dropped.
        $budget = $request->string('budget')->toString() ?: null;

        if (! ContactFormToken::isValid($request->string('form_token')->toString())
            || $request->filled('website')
            || ! $this->budgetIsFromForm($budget)) {
            return back()->with('status', __('site.contact_success'));
        }

        $attributes = [];

        foreach (is_array($data) ? $data : [] as $key => $value) {
            $attributes[(string) $key] = $value;
        }

        $submission = ContactSubmission::create($attributes);

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

            $this->acknowledge($submission, $profileEmail);
        }

        return back()->with('status', __('site.contact_success'));
    }

    /**
     * Confirms receipt to the sender.
     *
     * Only reached once every bot signal has passed, because the checks above
     * return early. That ordering is the whole safety story: this email goes
     * to an address the submitter chose, so a dropped submission must stay
     * silent rather than become a way to mail strangers.
     *
     * Queued, so a slow mail host cannot hold the visitor's request open, and
     * wrapped like the owner notification so a failure is logged rather than
     * surfaced: the message is already saved to the inbox either way.
     */
    private function acknowledge(ContactSubmission $submission, string $ownerEmail): void
    {
        try {
            Mail::to($submission->email)
                ->locale(app()->getLocale())
                ->queue(new ContactAcknowledgement($submission, $ownerEmail));
        } catch (\Throwable $e) {
            report($e);
        }
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
