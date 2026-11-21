<?php

namespace App\Http\Controllers;

use App\Models\UserPanelSetting;
use Illuminate\Http\Request;

class UserPanelSettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');
        $settings = $user->panelSettings()->firstOrCreate([], UserPanelSetting::DEFAULTS);

        return view('users.color-settings.index', compact('user', 'settings'));
    }

    public function save(Request $request)
    {
        $settings = $request->user('user')->panelSettings()->firstOrCreate([], UserPanelSetting::DEFAULTS);
        $settings->update($this->validatedColors($request));

        return redirect()->route('user.color-settings.index')->with('status', 'Panel colors saved successfully.');
    }

    public function reset(Request $request)
    {
        $request->user('user')->panelSettings()->updateOrCreate([], UserPanelSetting::DEFAULTS);

        return redirect()->route('user.color-settings.index')->with('status', 'Panel colors reset to the default theme.');
    }

    private function validatedColors(Request $request): array
    {
        return $request->validate([
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'panel_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
    }
}
