<?php

namespace App\Http\Controllers;

use App\Models\CarePartnerRegistrationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCarePartnerRegistrationController extends Controller
{
    private const STATUSES = [
        'pending' => 'Pending',
        'contacted' => 'Contacted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    private const PLANS = [
        'website' => 'Website only',
        'listing' => 'MediCare listing only',
        'website_listing' => 'Website + MediCare listing',
        'api_integration' => 'API & backend integration',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_merge(['all'], array_keys(self::STATUSES)))],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $requests = CarePartnerRegistrationRequest::query();

        if ($search !== '') {
            $requests->where(function ($query) use ($search): void {
                $query->where('practice_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $requests->where('status', $status);
        }

        return view('admin.registration-requests.index', [
            'requests' => $requests->latest()->paginate(15)->withQueryString(),
            'filters' => ['q' => $search, 'status' => $status],
            'statuses' => self::STATUSES,
            'plans' => self::PLANS,
            'counts' => CarePartnerRegistrationRequest::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function show(CarePartnerRegistrationRequest $registrationRequest): View
    {
        return view('admin.registration-requests.show', [
            'registrationRequest' => $registrationRequest,
            'statuses' => self::STATUSES,
            'planLabel' => self::PLANS[$registrationRequest->plan] ?? $registrationRequest->plan,
        ]);
    }

    public function updateStatus(Request $request, CarePartnerRegistrationRequest $registrationRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $registrationRequest->update($validated);

        return redirect()
            ->route('admin.registration-requests.show', $registrationRequest)
            ->with('status', 'Registration request status updated.');
    }
}
