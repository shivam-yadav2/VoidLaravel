<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeatureCardController extends Controller
{
    public function index(Request $request): View
    {
        $group = $request->query('group', FeatureCard::GROUP_SERVICES);
        $group = in_array($group, FeatureCard::GROUPS, true) ? $group : FeatureCard::GROUP_SERVICES;

        return view('admin.feature-cards.index', [
            'cards' => FeatureCard::group($group)->orderBy('sort_order')->get(),
            'group' => $group,
        ]);
    }

    public function create(Request $request): View
    {
        $group = in_array($request->query('group'), FeatureCard::GROUPS, true) ? $request->query('group') : FeatureCard::GROUP_SERVICES;

        return view('admin.feature-cards.form', [
            'card' => new FeatureCard(['is_active' => true, 'group' => $group]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($icon = $this->storeIcon($request)) {
            $data['icon'] = $icon;
        }

        $card = FeatureCard::create($data);

        return redirect()
            ->route('admin.feature-cards.index', ['group' => $card->group])
            ->with('admin_success', 'Card added.');
    }

    public function edit(FeatureCard $featureCard): View
    {
        return view('admin.feature-cards.form', ['card' => $featureCard]);
    }

    public function update(Request $request, FeatureCard $featureCard): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->boolean('remove_icon') && $featureCard->icon) {
            Storage::disk('public')->delete($featureCard->icon);
            $data['icon'] = null;
        }

        if ($icon = $this->storeIcon($request)) {
            if ($featureCard->icon) {
                Storage::disk('public')->delete($featureCard->icon);
            }
            $data['icon'] = $icon;
        }

        $featureCard->update($data);

        return redirect()
            ->route('admin.feature-cards.index', ['group' => $featureCard->group])
            ->with('admin_success', 'Card saved.');
    }

    public function destroy(FeatureCard $featureCard): RedirectResponse
    {
        $group = $featureCard->group;

        if ($featureCard->icon) {
            Storage::disk('public')->delete($featureCard->icon);
        }

        $featureCard->delete();

        return redirect()->route('admin.feature-cards.index', ['group' => $group])->with('admin_success', 'Card deleted.');
    }

    private function storeIcon(Request $request): ?string
    {
        return $request->hasFile('icon') ? $request->file('icon')->store('feature-cards', 'public') : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'group' => ['required', Rule::in(FeatureCard::GROUPS)],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'image', 'max:1024'],
        ]);

        unset($data['icon']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
