<?php

namespace App\Http\Controllers;

use App\Models\CarePartnerRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CarePartnerRegistrationController extends Controller
{
    private const PLANS = [
        'website' => 'Website only',
        'listing' => 'MediCare listing only',
        'website_listing' => 'Website + MediCare listing',
        'api_integration' => 'API & backend integration',
    ];

    private const PLAN_DETAILS = [
        'website' => 'A responsive practice website, with content managed from your MediCare workspace. Hosting and domain renewals are quoted separately.',
        'listing' => 'A MediCare practice profile where you can showcase your services and packages.',
        'website_listing' => 'A dedicated practice website together with a MediCare profile, services, and packages.',
        'api_integration' => 'API token and domain binding setup plus integration guidance for an existing website. Ongoing maintenance is quoted separately.',
    ];

    public function create(Request $request): View
    {
        return view('registration.create', [
            'plans' => self::PLANS,
            'planDetails' => self::PLAN_DETAILS,
            'selectedPlan' => $request->query('plan'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', Rule::in(array_keys(self::PLANS))],
            'practice_type' => ['required', Rule::in(['doctor', 'clinic', 'hospital', 'other'])],
            'practice_name' => ['required', 'string', 'max:160'],
            'specialty' => ['nullable', 'string', 'max:160'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'address' => ['required', 'string', 'max:2000'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
            'contact_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ]);

        unset($validated['consent']);
        $validated['consented_at'] = Carbon::now();
        $validated['status'] = 'pending';

        CarePartnerRegistrationRequest::create($validated);

        return redirect()
            ->route('care-partner-registration.create')
            ->with('registration_submitted', true);
    }
}
