<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadReceived;
use App\Models\Lead;
use App\Rules\Recaptcha;
use App\Support\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * Store an inbound enquiry from the public contact form.
     */
    public function store(Request $request, SiteConfig $site): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'company' => ['nullable', 'string', 'max:150'],
            'budget' => ['nullable', Rule::in(Lead::BUDGETS)],
            'brief' => ['required', 'string', 'max:5000'],
            // Honeypot: bots fill every field, humans never see this one.
            'website' => ['prohibited'],
        ];

        // Only required when an admin has actually configured reCAPTCHA at
        // Settings > Integrations — otherwise the form has no widget to fill in.
        if ($site->recaptcha_site_key && $site->recaptcha_secret_key) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha()];
        }

        $validated = $request->validate($rules);
        unset($validated['g-recaptcha-response']);

        $lead = Lead::create([
            ...$validated,
            'status' => Lead::STATUS_NEW,
            'ip_address' => $request->ip(),
        ]);

        $this->notifyTeam($lead, $site);

        return redirect()
            ->route('contact')
            ->with('contact_success', 'Thanks — your message is in. We reply within one business day.')
            ->withFragment('contact-form');
    }

    /**
     * The lead is already saved, so a mail failure (SMTP down, bad
     * credentials) is logged rather than shown to the visitor as an error.
     */
    private function notifyTeam(Lead $lead, SiteConfig $site): void
    {
        $to = $site->lead_notification_email ?: $site->contact_email;

        if (! $to) {
            return;
        }

        try {
            Mail::to($to)->send(new NewLeadReceived($lead));
        } catch (\Throwable $e) {
            Log::error('New-lead notification email failed.', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
        }
    }
}
