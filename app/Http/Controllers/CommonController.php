<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Support\Str;

class CommonController extends Controller
{
    public function definitions()
    {
       $definitions = [
                'message' => 'These definitions are provided only for help and guidance purposes. Use them to understand available options and pass the appropriate parameters when calling other APIs according to your requirements.',

                'services' => [
                    'id' => 1,
                    'description' => 'Use subcategory to specify a particular subcategory when searching for related healthcare services or information.',
                ],

                'doctor' => [
                    'id' => 2,
                    'description' => 'Use doctor to search for a specific doctor, medical specialist, or healthcare professional.',
                ],

                'hospital' => [
                    'id' => 3,
                    'description' => 'Use hospital to search for a specific hospital or healthcare facility.',
                ],

                'service-details' => [
                    'id' => 4,
                    'description' => 'Use service-details to search for information about a specific healthcare service.',
                ],

                'packages' => [
                    'id' => 5,
                    'description' => 'Use packages to search for information about specific healthcare packages.',
                ],
            ];

        return response()->json($definitions);
    }

    public function categories()
    {
        $categories = Category::where('is_active', 1)
            ->get()
            ->map(function ($category) {
                $category->url = Str::slug($category->name);
                return $category;
            });

        return response()->json($categories);
    }

    public function categoryData(Request $request, $category_url)
    {
        $payload = $request->header('payload');

        // if(empty($payload)) {
        //     return response()->json(['error' => 'Payload header is missing'], 400);
        // }

        if($payload == 4){
            $service = Service::where('is_active', 1)
                ->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($category_url)])
                ->first();

            return response()->json($service);
        }

        $category = Category::where('is_active', 1)
            ->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($category_url)])
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        if($payload == 1){
            $services = Service::where('is_active', 1)
                ->get()
                ->map(function ($service) {
                    $service->url = Str::slug($service->name);
                    return $service;
                });

            return response()->json($services);
        }else if($payload == 2){
            $get_users = $this->get_users(2);

            $doctors = Doctor::where('is_active', 1)->whereIn('user_id', $get_users)->where('category_id', $category->id)
                ->get()
                ->map(function ($doctor) {
                    $doctor->url = Str::slug($doctor->name);
                    $doctor->is_active = $this->isDoctorAvailableNow($doctor) ? 1 : 0;
                    return $doctor;
                });

            return response()->json($doctors);
        }else if($payload == 3){
            $get_users = $this->get_users(1);
            $hospitals = Hospital::where('is_active', 1)->whereIn('user_id', $get_users)
                ->get()
                ->map(function ($hospital) {
                    $hospital->url = Str::slug($hospital->name);
                    return $hospital;
                });
            return response()->json($hospitals);
        }else if($payload == 5){
            $get_users = $this->get_users(1);
            $packages = UserPackage::where('is_active', 1)
                ->whereIn('user_id', $get_users)
                ->with(['services' => function ($query) {
                    $query->where('is_active', 1);
                }])
                ->get()
                ->map(function ($package) {
                    $package->url = Str::slug($package->name);

                    return $package;
                });

            return response()->json($packages);
        }

        $category->url = Str::slug($category->name);

        return response()->json($category);
    }

    public function get_users($user_type)
    {
        $userIds = User::where('role', $user_type) ->whereIn('api_access', [1, 2])->get()->pluck('id');
        return $userIds;
    }

    public function doctors()
    {
        $get_users = $this->get_users(2);
        $doctors = Doctor::where('is_active', 1)
            ->whereIn('user_id', $get_users)
            ->get()
            ->map(function ($doctor) {
                $doctor->url = Str::slug($doctor->name);
                $doctor->is_active = $this->isDoctorAvailableNow($doctor) ? 1 : 0;

                return $doctor;
            });

        return response()->json($doctors);
    }

    /**
     * Determine the doctor's effective availability without changing the
     * administrator-controlled is_active value in the database.
     */
    private function isDoctorAvailableNow(Doctor $doctor): bool
    {
        if (! $doctor->opening_time || ! $doctor->closing_time) {
            return true;
        }

        // Clinic hours in this application are maintained in India time.
        $currentTime = now('Asia/Kolkata')->format('H:i:s');
        $openingTime = $doctor->opening_time->format('H:i:s');
        $closingTime = $doctor->closing_time->format('H:i:s');

        if ($openingTime === $closingTime) {
            return true;
        }

        if ($openingTime < $closingTime) {
            return $currentTime >= $openingTime && $currentTime < $closingTime;
        }

        return $currentTime >= $openingTime || $currentTime < $closingTime;
    }

}
