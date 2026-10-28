<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserGalleryImage;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function galleries(Request $request)
    {
        $images = $request->user()->galleryImages()
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn ($image): array => [
                'id' => $image->id,
                'title' => $image->title,
                'caption' => $image->caption,
                'image_url' => $this->imageUrl($image),
            ]);

        return response()->json([
            'error' => false,
            'data' => $images,
        ]);
    }

    private function imageUrl(UserGalleryImage $image): string
    {
        if (str_starts_with($image->image_path, 'user-gallery/')) {
            return $image->image_url;
        }

        $path = ltrim($image->image_path, '/');
        if (! str_starts_with($path, 'public/')) {
            $path = 'public/'.$path;
        }

        return asset($path);
    }
}
