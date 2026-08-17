<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function index(): View
    {
        return view('admin.seo.index', [
            'pages' => config('site.seo_pages'),
            'records' => SeoMeta::all()->keyBy('key'),
        ]);
    }

    public function edit(string $key): View
    {
        abort_unless(array_key_exists($key, config('site.seo_pages')), 404);

        return view('admin.seo.edit', [
            'key' => $key,
            'page' => config("site.seo_pages.{$key}"),
            'record' => SeoMeta::firstOrNew(['key' => $key]),
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        abort_unless(array_key_exists($key, config('site.seo_pages')), 404);

        $validated = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'remove_og_image' => ['nullable', 'boolean'],
        ]);

        $record = SeoMeta::firstOrNew(['key' => $key]);

        $record->fill([
            'key' => $key,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'noindex' => $request->boolean('noindex'),
        ]);

        if ($request->boolean('remove_og_image') && $record->og_image) {
            Storage::disk('public')->delete($record->og_image);
            $record->og_image = null;
        }

        if ($request->hasFile('og_image')) {
            if ($record->og_image) {
                Storage::disk('public')->delete($record->og_image);
            }
            $record->og_image = $request->file('og_image')->store('seo', 'public');
        }

        $record->save();

        return redirect()
            ->route('admin.seo.index')
            ->with('admin_success', 'Meta tags updated for “'.config("site.seo_pages.{$key}.label").'”.');
    }
}
