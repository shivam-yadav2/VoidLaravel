<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Rules\Recaptcha;
use App\Support\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        Lead::create([
            ...$validated,
            'status' => Lead::STATUS_NEW,
            'ip_address' => $request->ip(),
        ]);

        return back()
            ->with('contact_success', 'Thanks — your message is in. We reply within one business day.')
            ->withFragment('contact-form');
    }
}
