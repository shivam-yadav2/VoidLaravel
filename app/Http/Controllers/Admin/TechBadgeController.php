<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TechBadgeController extends Controller
{
    public function index(): View
    {
        return view('admin.tech-badges.index', ['badges' => TechBadge::orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.tech-badges.form', ['badge' => new TechBadge(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($icon = $this->storeIcon($request)) {
            $data['icon'] = $icon;
        }

        TechBadge::create($data);

        return redirect()->route('admin.tech-badges.index')->with('admin_success', 'Badge added.');
    }

    public function edit(TechBadge $techBadge): View
    {
        return view('admin.tech-badges.form', ['badge' => $techBadge]);
    }

    public function update(Request $request, TechBadge $techBadge): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->boolean('remove_icon') && $techBadge->icon) {
            Storage::disk('public')->delete($techBadge->icon);
            $data['icon'] = null;
        }

        if ($icon = $this->storeIcon($request)) {
            if ($techBadge->icon) {
                Storage::disk('public')->delete($techBadge->icon);
            }
            $data['icon'] = $icon;
        }

        $techBadge->update($data);

        return redirect()->route('admin.tech-badges.index')->with('admin_success', 'Badge saved.');
    }

    public function destroy(TechBadge $techBadge): RedirectResponse
    {
        if ($techBadge->icon) {
            Storage::disk('public')->delete($techBadge->icon);
        }

        $techBadge->delete();

        return redirect()->route('admin.tech-badges.index')->with('admin_success', 'Badge deleted.');
    }

    private function storeIcon(Request $request): ?string
    {
        return $request->hasFile('icon') ? $request->file('icon')->store('tech-badges', 'public') : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'image', 'max:512'],
        ]);

        unset($data['icon']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
