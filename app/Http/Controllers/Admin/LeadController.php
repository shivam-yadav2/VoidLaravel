<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $leads = Lead::query()
            ->when(in_array($status, Lead::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'status' => $status,
            'q' => $request->query('q'),
            'counts' => [
                'all' => Lead::count(),
                'new' => Lead::where('status', Lead::STATUS_NEW)->count(),
                'contacted' => Lead::where('status', Lead::STATUS_CONTACTED)->count(),
                'archived' => Lead::where('status', Lead::STATUS_ARCHIVED)->count(),
                'unread' => Lead::whereNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * Streams a CSV rather than building it in memory, so an export doesn't
     * balloon memory usage as the leads table grows. Honors the same
     * status/search filters as the index, so exporting from a filtered view
     * exports just that filter.
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');

        $leads = Lead::query()
            ->when(in_array($status, Lead::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->cursor();

        $filename = 'leads-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Company', 'Budget', 'Brief', 'Status', 'Received At']);

            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->name,
                    $lead->email,
                    $lead->company,
                    $lead->budget,
                    $lead->brief,
                    $lead->status,
                    $lead->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function show(Lead $lead): View
    {
        if ($lead->isUnread()) {
            $lead->forceFill(['read_at' => now()])->save();
        }

        return view('admin.leads.show', ['lead' => $lead]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Lead::STATUSES)],
        ]);

        $lead->update($validated);

        return back()->with('admin_success', 'Lead marked as '.$validated['status'].'.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()
            ->route('admin.leads.index')
            ->with('admin_success', 'Lead deleted.');
    }
}
