<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(SiteConfig $site): View
    {
        return view('admin.settings', [
            'groups' => config('site.groups'),
            'site' => $site,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $groups = config('site.groups');

        // Build validation rules straight from the declared field types so the
        // form and its rules can never drift apart.
        $rules = [];
        foreach ($groups as $group) {
            foreach ($group['fields'] as $key => $field) {
                $rules[$key] = match ($field['type']) {
                    'email' => ['nullable', 'email', 'max:190'],
                    'url' => ['nullable', 'url', 'max:255'],
                    'textarea' => ['nullable', 'string', 'max:5000'],
                    default => ['nullable', 'string', 'max:255'],
                };
            }
        }

        $validated = $request->validate($rules);

        Setting::setMany($validated);

        return back()->with('admin_success', 'Settings saved.');
    }
}
