<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'seo' => [
                'key' => 'home',
                'title' => 'Home',
                'description' => null, // falls back to the site description
            ],
        ]);
    }

    public function services(): View
    {
        return view('pages.services', [
            'seo' => [
                'key' => 'services',
                'title' => 'Services',
                'description' => 'Custom software development, ERP & HRMS solutions, web applications, business process automation and AI-powered systems — designed, built and operated by Void Layer.',
            ],
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'seo' => [
                'key' => 'about',
                'title' => 'About Us',
                'description' => 'Void Layer is a small, Lucknow-based team of senior developers building custom software, ERP & HRMS systems and automation — and staying on to operate them after launch.',
            ],
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'seo' => [
                'key' => 'contact',
                'title' => 'Contact Us',
                'description' => 'Tell us what you are building. We reply to every enquiry within one business day.',
            ],
        ]);
    }
}
