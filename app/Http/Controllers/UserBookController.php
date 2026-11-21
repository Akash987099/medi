<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserBookController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        return view('users.books.index', [
            'user' => $user,
            'books' => $user->books()->with('galleryImages')->latest()->get(),
            'galleryImages' => $user->bookGalleryImages()->with('book:id,title')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedBook($request, true);
        $user = $request->user('user');
        $validated['cover_path'] = $request->hasFile('cover') ? $this->storeFile($request->file('cover'), $user->id, 'covers') : null;
        $validated['ebook_pdf_path'] = $request->hasFile('ebook_pdf') ? $this->storeFile($request->file('ebook_pdf'), $user->id, 'ebooks') : null;
        unset($validated['cover'], $validated['ebook_pdf'], $validated['copyright_confirmation']);
        $user->books()->create($validated);

        return redirect()->route('user.books.index')->with('status', 'Book added to your catalog successfully.');
    }

    public function update(Request $request, int $book)
    {
        $item = $request->user('user')->books()->findOrFail($book);
        $validated = $this->validatedBook($request, false);
        $userId = $request->user('user')->id;

        if ($request->hasFile('cover')) {
            $this->deleteFile($item->cover_path, $userId);
            $validated['cover_path'] = $this->storeFile($request->file('cover'), $userId, 'covers');
        }
        if ($request->hasFile('ebook_pdf')) {
            $this->deleteFile($item->ebook_pdf_path, $userId);
            $validated['ebook_pdf_path'] = $this->storeFile($request->file('ebook_pdf'), $userId, 'ebooks');
        }
        unset($validated['cover'], $validated['ebook_pdf'], $validated['copyright_confirmation']);
        $item->update($validated);

        return redirect()->route('user.books.index')->with('status', 'Book updated successfully.');
    }

    public function toggleStatus(Request $request, int $book)
    {
        $item = $request->user('user')->books()->findOrFail($book);
        $item->update(['is_active' => ! $item->is_active]);

        return redirect()->route('user.books.index')->with('status', 'Book '.($item->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $book)
    {
        $item = $request->user('user')->books()->findOrFail($book);
        $this->deleteFile($item->cover_path, $request->user('user')->id);
        $this->deleteFile($item->ebook_pdf_path, $request->user('user')->id);
        $item->delete();

        return redirect()->route('user.books.index')->with('status', 'Book removed from your catalog.');
    }

    public function storeGalleryImage(Request $request)
    {
        $validated = $request->validate([
            'user_book_id' => ['required', 'integer'],
            'images' => ['required', 'array', 'min:1', 'max:12'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:220'],
        ]);
        $user = $request->user('user');
        $book = $user->books()->findOrFail($validated['user_book_id']);
        foreach ($request->file('images') as $image) {
            $book->galleryImages()->create([
                'user_id' => $user->id,
                'image_path' => $this->storeFile($image, $user->id, 'gallery'),
                'caption' => $validated['caption'] ?? null,
            ]);
        }

        return redirect()->route('user.books.index', ['tab' => 'gallery'])->with('status', 'Gallery image(s) added successfully.');
    }

    public function destroyGalleryImage(Request $request, int $image)
    {
        $item = $request->user('user')->bookGalleryImages()->findOrFail($image);
        $this->deleteFile($item->image_path, $request->user('user')->id);
        $item->delete();

        return redirect()->route('user.books.index', ['tab' => 'gallery'])->with('status', 'Gallery image removed successfully.');
    }

    public function read(Request $request, int $book): BinaryFileResponse
    {
        $item = $request->user('user')->books()->findOrFail($book);
        abort_unless($item->format === 'ebook' && $item->ebook_pdf_path, 404);
        $path = public_path($item->ebook_pdf_path);
        abort_unless(File::isFile($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.Str::slug($item->title).'.pdf"']);
    }

    private function validatedBook(Request $request, bool $isCreating): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:220'], 'subtitle' => ['nullable', 'string', 'max:220'],
            'authors' => ['required', 'string', 'max:500'], 'publisher' => ['nullable', 'string', 'max:180'],
            'isbn' => ['nullable', 'string', 'max:30'], 'language' => ['required', 'string', 'max:80'],
            'publication_date' => ['nullable', 'date'], 'format' => ['required', 'in:hardback,paperback,ebook'],
            'description' => ['nullable', 'string', 'max:8000'], 'inventory_quantity' => ['required', 'integer', 'min:0', 'max:9999999'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'], 'is_active' => ['required', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ebook_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ];
        if ($isCreating) {
            $rules['copyright_confirmation'] = ['accepted'];
        }
        $validated = $request->validate($rules);
        if ($validated['format'] === 'ebook' && $isCreating && ! $request->hasFile('ebook_pdf')) {
            $request->validate(['ebook_pdf' => ['required', 'file', 'mimes:pdf', 'max:20480']]);
        }

        return $validated;
    }

    private function storeFile(UploadedFile $file, int $userId, string $folder): string
    {
        $directory = public_path($userId.'/books/'.$folder);
        File::ensureDirectoryExists($directory, 0755, true);
        $filename = Str::uuid()->toString().'.'.$file->extension();
        $file->move($directory, $filename);

        return $userId.'/books/'.$folder.'/'.$filename;
    }

    private function deleteFile(?string $path, int $userId): void
    {
        if (! $path || ! str_starts_with(str_replace('\\', '/', $path), $userId.'/books/')) return;
        $absolutePath = public_path($path);
        if (File::isFile($absolutePath)) File::delete($absolutePath);
    }
}
