<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
class CommonController extends Controller
{
    public function categories()
    {
        $categories = Category::where('is_active', 1)->get();
        return response()->json($categories);
    }

}
