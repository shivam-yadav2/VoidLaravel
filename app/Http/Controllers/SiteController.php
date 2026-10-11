<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        $faqs = Faq::active()->get();

        return view('pages.home', [
            'seo' => [
                'key' => 'home',
                // No page title: the homepage <title> is "Site name — tagline",
                // not "Home | Site name", which wastes the most valuable title.
                'title' => null,
                'description' => null, // falls back to the site description
                'jsonld' => $faqs->isEmpty() ? null : [
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqs->map(fn (Faq $faq) => [
                        '@type' => 'Question',
                        'name' => $faq->question,
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                    ])->all(),
                ],
            ],
        ]);
    }

    public function services(): View
    {
        return view('pages.services', [
            'seo' => [
                'key' => 'services',
                'title' => 'Custom Software, ERP & HRMS Development Services',
                'description' => 'Custom software development, ERP & HRMS solutions, web applications, business process automation and AI-powered systems — designed, built and operated by Void Layer.',
                'breadcrumbs' => $this->crumbs('Services', route('services')),
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
                'breadcrumbs' => $this->crumbs('About', route('about')),
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
                'breadcrumbs' => $this->crumbs('Contact', route('contact')),
            ],
        ]);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function crumbs(string $name, string $url): array
    {
        return [['Home', route('home')], [$name, $url]];
    }
}
