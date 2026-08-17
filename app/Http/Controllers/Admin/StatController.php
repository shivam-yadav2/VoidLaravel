<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StatController extends Controller
{
    public function index(): View
    {
        return view('admin.stats.index', ['stats' => Stat::orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.stats.form', ['stat' => new Stat(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($icon = $this->storeIcon($request)) {
            $data['icon'] = $icon;
        }

        Stat::create($data);

        return redirect()->route('admin.stats.index')->with('admin_success', 'Stat added.');
    }

    public function edit(Stat $stat): View
    {
        return view('admin.stats.form', ['stat' => $stat]);
    }

    public function update(Request $request, Stat $stat): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->boolean('remove_icon') && $stat->icon) {
            Storage::disk('public')->delete($stat->icon);
            $data['icon'] = null;
        }

        if ($icon = $this->storeIcon($request)) {
            if ($stat->icon) {
                Storage::disk('public')->delete($stat->icon);
            }
            $data['icon'] = $icon;
        }

        $stat->update($data);

        return redirect()->route('admin.stats.index')->with('admin_success', 'Stat saved.');
    }

    public function destroy(Stat $stat): RedirectResponse
    {
        if ($stat->icon) {
            Storage::disk('public')->delete($stat->icon);
        }

        $stat->delete();

        return redirect()->route('admin.stats.index')->with('admin_success', 'Stat deleted.');
    }

    private function storeIcon(Request $request): ?string
    {
        return $request->hasFile('icon') ? $request->file('icon')->store('stats', 'public') : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:30'],
            'label' => ['required', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'image', 'max:1024'],
        ]);

        unset($data['icon']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
