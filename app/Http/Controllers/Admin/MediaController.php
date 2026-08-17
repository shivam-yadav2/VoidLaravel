<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        return view('admin.media.index', [
            'media' => Media::latest()->paginate(24),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['image', 'max:4096'],
        ]);

        foreach ($request->file('files', []) as $file) {
            $path = $file->store('media', 'public');

            Media::create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return back()->with('admin_success', 'Uploaded.');
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $request->validate(['alt_text' => ['nullable', 'string', 'max:255']]);

        $medium->update(['alt_text' => $request->input('alt_text')]);

        return back()->with('admin_success', 'Updated.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        Storage::disk('public')->delete($medium->path);
        $medium->delete();

        return back()->with('admin_success', 'Deleted.');
    }
}
