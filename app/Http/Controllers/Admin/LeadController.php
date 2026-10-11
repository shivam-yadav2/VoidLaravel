<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Support\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = $this->filtered($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'status' => $request->query('status'),
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
        $leads = $this->filtered($request)->latest()->cursor();

        $filename = 'leads-'.now()->displayTz()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');

            // UTF-8 byte-order mark: without it Excel reads the file as
            // Windows-1252 and mangles "₹", "–" and any non-Latin name.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Name', 'Email', 'Company', 'Budget', 'Brief', 'Status', 'Received At (IST)']);

            foreach ($leads as $lead) {
                fputcsv($out, array_map([self::class, 'csvSafe'], [
                    $lead->name,
                    $lead->email,
                    $lead->company,
                    $lead->budget,
                    $lead->brief,
                    $lead->status,
                    $lead->created_at->displayTz()->format('Y-m-d H:i'),
                ]));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Every text field here comes from the public contact form. A value
     * starting with = + - @ (or a tab/CR) is executed as a formula when the
     * CSV is opened in Excel/Sheets ("CSV injection"), so prefix it with an
     * apostrophe to force it to be read as plain text.
     */
    public static function csvSafe(?string $value): ?string
    {
        if ($value !== null && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    private function filtered(Request $request): Builder
    {
        $status = $request->query('status');
        $term = trim((string) $request->query('q'));

        return Lead::query()
            ->when(in_array($status, Lead::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', Like::contains($term))
                        ->orWhere('email', 'like', Like::contains($term))
                        ->orWhere('company', 'like', Like::contains($term));
                });
            });
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
