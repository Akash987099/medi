<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');
        $profile = $user->role === 1
            ? Hospital::where('user_id', $user->id)->first()
            : Doctor::with(['category', 'hospital'])->where('user_id', $user->id)->first();

        return view('users.dashboard', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}
