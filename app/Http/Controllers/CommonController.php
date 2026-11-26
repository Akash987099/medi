<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CommonController extends Controller
{
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
