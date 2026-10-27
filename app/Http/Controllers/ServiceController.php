<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Support\SvgSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $serviceQuery = Service::with('category')->latest();
        if (! empty($validated['category_id'])) {
            $serviceQuery->where('category_id', $validated['category_id']);
        }

        return view('service.index', [
            'services' => $serviceQuery->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'selectedCategoryId' => $validated['category_id'] ?? '',
            'activeCategories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateService($request);
        $visualValue = $this->resolveVisualValue($request, $validated);

        Service::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'visual_type' => $validated['visual_type'],
            'visual_value' => $visualValue,
            'is_active' => true,
        ]);

        return redirect()
            ->route('service.index')
            ->with('status', 'Service added successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $this->validateService($request, $service);
        $visualValue = $this->resolveVisualValue($request, $validated, $service);

        $service->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'visual_type' => $validated['visual_type'],
            'visual_value' => $visualValue,
        ]);

        return redirect()
            ->route('service.index', $request->only('category_id'))
            ->with('status', 'Service updated successfully.');
    }

    public function toggleStatus(Request $request, Service $service)
    {
        $service->is_active = ! $service->is_active;
        $service->save();

        return redirect()
            ->route('service.index', $request->only('category_id'))
            ->with('status', 'Service '.($service->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    private function validateService(Request $request, ?Service $service = null): array
    {
        $uniqueName = Rule::unique('services', 'name')
            ->where('category_id', $request->input('category_id'));
        if ($service) {
            $uniqueName->ignore($service->id);
        }

        return $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:120', $uniqueName],
            'visual_type' => ['required', 'in:image,svg,icon'],
            'image' => [
                $request->input('visual_type') === 'image'
                    && (! $service || $service->visual_type !== 'image') ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'svg_code' => ['required_if:visual_type,svg', 'nullable', 'string', 'max:20000'],
            'icon_class' => ['required_if:visual_type,icon', 'nullable', 'regex:/^(fas|far|fab) fa-[a-z0-9-]+$/', 'max:80'],
        ], [
            'category_id.exists' => 'Select an active category.',
            'icon_class.regex' => 'Enter an icon class such as “fas fa-heart”.',
        ]);
    }

    private function resolveVisualValue(Request $request, array $validated, ?Service $service = null): string
    {
        if ($validated['visual_type'] === 'image') {
            if (! $request->hasFile('image') && $service && $service->visual_type === 'image') {
                return $service->visual_value;
            }

            $visualValue = $request->file('image')->storePublicly('services', 'public');
            if ($visualValue === false) {
                throw new RuntimeException('The service image could not be saved.');
            }

            return $visualValue;
        }

        if ($validated['visual_type'] === 'svg') {
            return SvgSanitizer::sanitize($validated['svg_code']);
        }

        return $validated['icon_class'];
    }
}
