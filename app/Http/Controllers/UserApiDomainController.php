<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserApiDomain;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserApiDomainController extends Controller
{
    public function index()
    {
        return view('api-domains.index', [
            'domains' => UserApiDomain::with('user')
                ->whereHas('user')
                ->latest()
                ->get(),
            'users' => User::query()
                ->whereIn('role', [1, 2])
                ->whereNotNull('api_token')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'domain' => strtolower(rtrim(trim((string) $request->input('domain')), '.')),
        ]);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn (Builder $query) => $query
                        ->whereIn('role', [1, 2])
                        ->whereNotNull('api_token')),
            ],
            'domain' => [
                'required',
                'string',
                'max:253',
                'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
                Rule::unique('user_api_domains', 'domain_hash'),
            ],
        ], [
            'user_id.exists' => 'Select an account with an API token.',
            'domain.regex' => 'Enter a domain such as example.com, without a scheme, path, or port.',
        ]);

        UserApiDomain::create($validated);

        return redirect()->route('api-domains.index')->with('status', 'API domain added successfully.');
    }

    public function destroy(UserApiDomain $apiDomain)
    {
        $apiDomain->delete();

        return redirect()->route('api-domains.index')->with('status', 'API domain removed successfully.');
    }

    public function toggleStatus(UserApiDomain $apiDomain)
    {
        $apiDomain->is_active = ! $apiDomain->is_active;
        $apiDomain->save();

        return redirect()
            ->route('api-domains.index')
            ->with('status', 'API access for '.$apiDomain->domain.' '.($apiDomain->is_active ? 'activated' : 'deactivated').'.');
    }
}
