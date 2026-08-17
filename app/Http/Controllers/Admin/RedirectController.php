<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(): View
    {
        return view('admin.redirects.index', ['redirects' => Redirect::latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Redirect::create($data);

        return back()->with('admin_success', 'Redirect added.');
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        $redirect->update($this->validated($request, $redirect));

        return back()->with('admin_success', 'Redirect saved.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return back()->with('admin_success', 'Redirect deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Redirect $redirect = null): array
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255'],
            'to_path' => ['required', 'string', 'max:255'],
            'status_code' => ['required', Rule::in([301, 302])],
        ]);

        // Validate uniqueness against the *normalized* form, since the raw
        // input ("old-page" vs "/old-page/") isn't what's actually stored.
        $normalized = Redirect::normalize($data['from_path']);
        $clash = Redirect::where('from_path', $normalized)->when($redirect, fn ($q) => $q->where('id', '!=', $redirect->id))->exists();

        if ($clash) {
            throw ValidationException::withMessages([
                'from_path' => 'A redirect for this path already exists.',
            ]);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
