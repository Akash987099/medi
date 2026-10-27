<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\SvgSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class CategoryController extends Controller
{
    public function index()
    {
        return view('category.index', [
            'categories' => Category::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        $visualValue = $this->resolveVisualValue($request, $validated);

        Category::create([
            'name' => $validated['name'],
            'visual_type' => $validated['visual_type'],
            'visual_value' => $visualValue,
            'is_active' => true,
        ]);

        return redirect()
            ->route('category.index')
            ->with('status', 'Category added successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $this->validateCategory($request, $category);
        $visualValue = $this->resolveVisualValue($request, $validated, $category);

        $category->update([
            'name' => $validated['name'],
            'visual_type' => $validated['visual_type'],
            'visual_value' => $visualValue,
        ]);

        return redirect()
            ->route('category.index')
            ->with('status', 'Category updated successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return redirect()
            ->route('category.index')
            ->with('status', 'Category '.($category->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $visualType = $request->input('visual_type');
        $imageRequired = $visualType === 'image' && (!$category || $category->visual_type !== 'image');

        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($category?->id)],
            'visual_type' => ['required', 'in:image,svg,icon'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'svg_code' => ['required_if:visual_type,svg', 'nullable', 'string', 'max:20000'],
            'icon_class' => ['required_if:visual_type,icon', 'nullable', 'regex:/^(fas|far|fab) fa-[a-z0-9-]+$/', 'max:80'],
        ], [
            'icon_class.regex' => 'Enter an icon class such as “fas fa-heart”.',
        ]);
    }

    private function resolveVisualValue(Request $request, array $validated, ?Category $category = null): string
    {
        if ($validated['visual_type'] === 'image') {
            if (!$request->hasFile('image') && $category && $category->visual_type === 'image') {
                return $category->visual_value;
            }

            $path = $request->file('image')->storePublicly('categories', 'public');
            if ($path === false) {
                throw new RuntimeException('The category image could not be saved.');
            }

            return $path;
        }

        if ($validated['visual_type'] === 'svg') {
            return SvgSanitizer::sanitize($validated['svg_code']);
        }

        return $validated['icon_class'];
    }
}
