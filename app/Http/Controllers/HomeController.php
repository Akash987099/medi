<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'stats' => [
                ['value' => User::whereIn('role', [1, 2])->count(), 'label' => 'Care partner accounts'],
                ['value' => Doctor::where('is_active', true)->count(), 'label' => 'Active doctors'],
                ['value' => Hospital::where('is_active', true)->count(), 'label' => 'Active hospitals'],
                ['value' => Service::where('is_active', true)->count(), 'label' => 'Available services'],
                ['value' => Category::where('is_active', true)->count(), 'label' => 'Care categories'],
            ],
            'doctors' => Doctor::with('category')
                ->where('is_active', true)
                ->latest()
                ->limit(3)
                ->get(),
            'hospitals' => Hospital::where('is_active', true)
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
