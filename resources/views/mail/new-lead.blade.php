New enquiry from the website contact form.

Name:     {{ $lead->name }}
Email:    {{ $lead->email }}
Company:  {{ $lead->company ?: '—' }}
Budget:   {{ $lead->budget ?: '—' }}
Received: {{ $lead->created_at->displayTz()->format('d M Y, H:i T') }}

What they're building:
{{ $lead->brief }}

Open in admin: {{ route('admin.leads.show', $lead) }}
