<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Post;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'leadsThisWeek' => Lead::where('created_at', '>=', now()->subDays(7))->count(),
            'unreadLeads' => Lead::whereNull('read_at')->count(),
            'totalLeads' => Lead::count(),
            'recentLeads' => Lead::latest()->take(5)->get(),

            'publishedPosts' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
            'draftPosts' => Post::where('status', Post::STATUS_DRAFT)->count(),
            'topPosts' => Post::published()->orderByDesc('views')->take(5)->get(),
        ]);
    }
}
