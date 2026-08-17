<?php

/*
|--------------------------------------------------------------------------
| Site defaults
|--------------------------------------------------------------------------
|
| Fallbacks used whenever the matching row in the `settings` table is missing
| or blank. Admin edits at /admin/settings write to the database and take
| precedence; these keep the site rendering correctly on a fresh install.
|
*/

return [

    'defaults' => [
        // Identity
        'site_name' => 'Void Layer',
        'site_tagline' => 'Custom software, ERP & HRMS, and AI-powered automation',
        'site_description' => 'Void Layer helps businesses streamline operations through custom software, ERP & HRMS systems, web applications and AI-powered automation.',
        'site_og_image' => 'assets/img/void_logo.png',

        // Contact
        'contact_email' => 'hello@voidlayer.in',
        'contact_phone' => '9415729564',
        'contact_phone_link' => '+919415729564',
        'contact_address' => 'Lucknow, Uttar Pradesh',
        'contact_address_note' => 'Remote-friendly, working with clients everywhere',
        'contact_hours' => '8/7',

        // Social
        'social_instagram' => 'https://www.instagram.com/voidlayer.in',
        'social_linkedin' => 'https://www.linkedin.com/company/voidlayer-software-solutions',
        'social_whatsapp' => 'https://wa.me/919415729564',
        'social_facebook' => '',
        'social_twitter' => '',

        // Footer
        'footer_support_text' => 'Our support team is here to help you every step of the way. Whether you have questions, need assistance, or face any issues, we provide timely and reliable solutions to ensure a smooth experience.',
        'footer_tagline' => 'Built with intent and a little glow.',

        // Integrations — all optional, features stay off until an admin fills these in.
        'google_analytics_id' => '',
        'meta_pixel_id' => '',
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
        'custom_head_code' => '',
        'custom_body_code' => '',
    ],

    /*
    | Fixed pages whose meta tags are editable at /admin/seo. Content pages and
    | blog posts are excluded — they carry their own meta fields on their own
    | edit screens.
    */
    'seo_pages' => [
        'home' => ['label' => 'Home', 'route' => 'home'],
        'services' => ['label' => 'Services', 'route' => 'services'],
        'about' => ['label' => 'About', 'route' => 'about'],
        'contact' => ['label' => 'Contact', 'route' => 'contact'],
        'blog.index' => ['label' => 'Blog index', 'route' => 'blog.index'],
    ],

    /*
    | Groups rendered as sections on the admin settings screen.
    | label => [key => [label, type, help]]
    */
    'groups' => [
        'identity' => [
            'label' => 'Site identity',
            'fields' => [
                'site_name' => ['label' => 'Site name', 'type' => 'text'],
                'site_tagline' => ['label' => 'Tagline', 'type' => 'text'],
                'site_description' => ['label' => 'Default meta description', 'type' => 'textarea', 'help' => 'Used for SEO on pages without their own description.'],
            ],
        ],
        'contact' => [
            'label' => 'Contact details',
            'fields' => [
                'contact_email' => ['label' => 'Email', 'type' => 'email'],
                'contact_phone' => ['label' => 'Phone (display)', 'type' => 'text'],
                'contact_phone_link' => ['label' => 'Phone (dial format)', 'type' => 'text', 'help' => 'Used in tel: links, e.g. +919415729564'],
                'contact_address' => ['label' => 'Address', 'type' => 'text'],
                'contact_address_note' => ['label' => 'Address note', 'type' => 'text'],
                'contact_hours' => ['label' => 'Support hours', 'type' => 'text'],
            ],
        ],
        'social' => [
            'label' => 'Social links',
            'fields' => [
                'social_instagram' => ['label' => 'Instagram URL', 'type' => 'url'],
                'social_linkedin' => ['label' => 'LinkedIn URL', 'type' => 'url'],
                'social_whatsapp' => ['label' => 'WhatsApp URL', 'type' => 'url'],
                'social_facebook' => ['label' => 'Facebook URL', 'type' => 'url'],
                'social_twitter' => ['label' => 'X / Twitter URL', 'type' => 'url'],
            ],
        ],
        'footer' => [
            'label' => 'Footer',
            'fields' => [
                'footer_support_text' => ['label' => 'Support paragraph', 'type' => 'textarea'],
                'footer_tagline' => ['label' => 'Footer tagline', 'type' => 'text'],
            ],
        ],
        'integrations' => [
            'label' => 'Integrations',
            'fields' => [
                'google_analytics_id' => ['label' => 'Google Analytics ID', 'type' => 'text', 'help' => 'GA4 measurement ID, e.g. G-XXXXXXXXXX. Leave blank to disable.'],
                'meta_pixel_id' => ['label' => 'Meta (Facebook) Pixel ID', 'type' => 'text', 'help' => 'Numeric pixel ID. Leave blank to disable.'],
                'recaptcha_site_key' => ['label' => 'reCAPTCHA v2 site key', 'type' => 'text', 'help' => 'From google.com/recaptcha/admin. Adds a checkbox to the contact form. Leave blank to disable.'],
                'recaptcha_secret_key' => ['label' => 'reCAPTCHA v2 secret key', 'type' => 'text'],
                'custom_head_code' => ['label' => 'Custom code (before </head>)', 'type' => 'textarea', 'help' => 'Raw HTML/JS, e.g. an extra tracking snippet. Runs on every page — only paste code you trust.'],
                'custom_body_code' => ['label' => 'Custom code (before </body>)', 'type' => 'textarea', 'help' => 'Same as above, placed at the end of the page instead.'],
            ],
        ],
    ],
];
