<?php

namespace App\Http\Controllers;

use App\Models\UserVideo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserVideoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $videosQuery = $request->user('user')->videos();

        if ($search !== '') {
            $videosQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $videosQuery->where('is_active', $status === 'active');
        }

        return view('users.videos.index', [
            'videos' => $videosQuery->latest()->paginate(10)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function stream(Request $request, int $video): StreamedResponse
    {
        $userVideo = $this->findUserVideo($request, $video);
        abort_unless($userVideo->video_type === 'upload' && $userVideo->video_path, 404);

        $userId = $request->user('user')->id;
        $relativePath = str_replace('\\', '/', $userVideo->video_path);
        $prefix = $userId.'/';
        abort_unless(str_starts_with($relativePath, $prefix), 404);

        $filename = substr($relativePath, strlen($prefix));
        abort_unless($filename !== '' && ! str_contains($filename, '/') && ! str_contains($filename, '..'), 404);

        $filePath = public_path($relativePath);
        abort_unless(File::isFile($filePath) && File::isReadable($filePath), 404);

        $fileSize = File::size($filePath);
        $start = 0;
        $end = $fileSize - 1;
        $status = 200;
        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => File::mimeType($filePath) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        $rangeHeader = $request->header('Range');
        if ($rangeHeader !== null) {
            if (! preg_match('/^bytes=(\d*)-(\d*)$/', trim($rangeHeader), $matches) || $fileSize === 0) {
                return response()->stream(static function (): void {}, 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            if ($matches[1] === '' && $matches[2] === '') {
                return response()->stream(static function (): void {}, 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            if ($matches[1] === '') {
                $suffixLength = (int) $matches[2];
                if ($suffixLength === 0) {
                    return response()->stream(static function (): void {}, 416, [
                        'Content-Range' => "bytes */{$fileSize}",
                        'Accept-Ranges' => 'bytes',
                    ]);
                }
                $start = max(0, $fileSize - $suffixLength);
            } else {
                $start = (int) $matches[1];
                if ($matches[2] !== '') {
                    $end = (int) $matches[2];
                }
            }

            if ($start >= $fileSize || $end < $start) {
                return response()->stream(static function (): void {}, 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $end = min($end, $fileSize - 1);
            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = (string) $length;

        return response()->stream(function () use ($filePath, $start, $length): void {
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                throw new RuntimeException('The video file could not be opened for streaming.');
            }

            try {
                if (fseek($handle, $start) !== 0) {
                    throw new RuntimeException('The video file could not be positioned for streaming.');
                }

                $remaining = $length;
                while ($remaining > 0 && ! connection_aborted()) {
                    $chunk = fread($handle, min(8192, $remaining));
                    if ($chunk === false || $chunk === '') {
                        throw new RuntimeException('The video file could not be read completely.');
                    }

                    echo $chunk;
                    $remaining -= strlen($chunk);
                    flush();
                }
            } finally {
                fclose($handle);
            }
        }, $status, $headers);
    }

    public function store(Request $request)
    {
        $validated = $this->validateVideo($request);
        $user = $request->user('user');
        $videoPath = $validated['video_type'] === 'upload'
            ? $this->moveUploadedVideo($request->file('video'), $user->id)
            : null;
        $youtubeVideoId = $validated['video_type'] === 'youtube'
            ? $this->youtubeVideoId($validated['youtube_url'])
            : null;

        $user->videos()->create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'video_type' => $validated['video_type'],
            'video_path' => $videoPath,
            'youtube_video_id' => $youtubeVideoId,
        ]);

        return redirect()->route('user.videos.index')->with('status', 'Video added successfully.');
    }

    public function update(Request $request, int $video)
    {
        $userVideo = $this->findUserVideo($request, $video);
        $validated = $this->validateVideo($request, $userVideo->video_type !== 'upload');
        $oldPath = $userVideo->video_path;
        $newPath = $validated['video_type'] === 'upload'
            ? ($request->hasFile('video')
                ? $this->moveUploadedVideo($request->file('video'), $request->user('user')->id)
                : ($userVideo->video_type === 'upload' ? $oldPath : null))
            : null;
        $youtubeVideoId = $validated['video_type'] === 'youtube'
            ? $this->youtubeVideoId($validated['youtube_url'])
            : null;

        $userVideo->update([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'video_type' => $validated['video_type'],
            'video_path' => $newPath,
            'youtube_video_id' => $youtubeVideoId,
        ]);

        if ($oldPath && $oldPath !== $newPath) {
            $this->deleteUserVideo($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.videos.index')->with('status', 'Video updated successfully.');
    }

    public function toggleStatus(Request $request, int $video)
    {
        $userVideo = $this->findUserVideo($request, $video);
        $userVideo->is_active = ! $userVideo->is_active;
        $userVideo->save();

        return redirect()
            ->route('user.videos.index')
            ->with('status', 'Video '.($userVideo->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $video)
    {
        $userVideo = $this->findUserVideo($request, $video);
        if ($userVideo->video_path) {
            $this->deleteUserVideo($userVideo->video_path, $request->user('user')->id);
        }
        $userVideo->delete();

        return redirect()->route('user.videos.index')->with('status', 'Video deleted successfully.');
    }

    private function validateVideo(Request $request, bool $uploadRequired = true): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'video_type' => ['required', 'in:upload,youtube'],
            'video' => [
                $request->input('video_type') === 'upload'
                    ? ($uploadRequired ? 'required' : 'nullable')
                    : 'prohibited',
                'file',
                'mimetypes:video/mp4,video/webm,video/ogg',
                'max:51200',
            ],
            'youtube_url' => [
                $request->input('video_type') === 'youtube' ? 'required' : 'prohibited',
                'string',
                'max:2048',
                'url',
            ],
        ]);

        if (($validated['video_type'] ?? null) === 'youtube') {
            $videoId = $this->youtubeVideoId($validated['youtube_url']);
            if (! $videoId) {
                throw ValidationException::withMessages([
                    'youtube_url' => 'Enter a valid YouTube video link.',
                ]);
            }
        }

        return $validated;
    }

    private function youtubeVideoId(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $videoId = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            if ($path === 'watch') {
                parse_str($parts['query'] ?? '', $query);
                $videoId = $query['v'] ?? null;
            } elseif (preg_match('#^(?:embed|shorts|live)/([A-Za-z0-9_-]{11})$#', $path, $matches)) {
                $videoId = $matches[1];
            }
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true) && preg_match('#^([A-Za-z0-9_-]{11})$#', $path, $matches)) {
            $videoId = $matches[1];
        }

        return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)
            ? $videoId
            : null;
    }

    private function findUserVideo(Request $request, int $video): UserVideo
    {
        return $request->user('user')->videos()->findOrFail($video);
    }

    private function moveUploadedVideo(UploadedFile $video, int $userId): string
    {
        $directory = public_path((string) $userId);
        File::ensureDirectoryExists($directory, 0755, true);

        $filename = Str::uuid()->toString().'.'.$video->extension();
        $video->move($directory, $filename);

        return $userId.'/'.$filename;
    }

    private function deleteUserVideo(string $path, int $userId): void
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $prefix = $userId.'/';
        if (! str_starts_with($normalizedPath, $prefix)) {
            throw new RuntimeException('The video does not belong to the current user folder.');
        }

        $filename = substr($normalizedPath, strlen($prefix));
        if ($filename === '' || str_contains($filename, '..') || str_contains($filename, '/')) {
            throw new RuntimeException('The video path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous video could not be removed.');
        }
    }
}
