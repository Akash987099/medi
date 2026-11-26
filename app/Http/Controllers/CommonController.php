<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CommonController extends Controller
{
    public function definitions()
    {
       $definitions = [
                'message' => 'These definitions are provided only for help and guidance purposes. Use them to understand available options and pass the appropriate parameters when calling other APIs according to your requirements.',

                'subcategory' => [
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

}
