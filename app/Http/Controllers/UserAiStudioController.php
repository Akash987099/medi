<?php

namespace App\Http\Controllers;

use App\Models\UserAiConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserAiStudioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        return view('users.ai-studio.index', [
            'user' => $user,
            'conversations' => $user->aiConversations()->latest('updated_at')->limit(20)->get(['id', 'title', 'updated_at']),
        ]);
    }

    public function conversation(Request $request, int $conversation): JsonResponse
    {
        $item = $request->user('user')->aiConversations()
            ->with(['messages' => fn ($query) => $query->oldest()])
            ->findOrFail($conversation);

        return response()->json([
            'conversation' => [
                'id' => $item->id,
                'title' => $item->title,
                'messages' => $item->messages->map(fn ($message) => [
                    'role' => $message->role,
                    'content' => $message->content,
                ])->values(),
            ],
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        if (blank(config('services.gemini.key'))) {
            return response()->json(['message' => 'AI service is not configured yet. Add GEMINI_API_KEY to the server environment.'], 503);
        }

        $user = $request->user('user');
        $conversation = filled($validated['conversation_id'] ?? null)
            ? $user->aiConversations()->findOrFail($validated['conversation_id'])
            : null;

        $messages = ($conversation?->messages()->latest()->take(12)->get() ?? collect())->reverse()
            ->map(fn ($item) => [
                'role' => $item->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $item->content]],
            ])
            ->push(['role' => 'user', 'parts' => [['text' => $validated['message']]]])
            ->values()
            ->all();

        try {
            $response = Http::acceptJson()
                ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(60)
                ->post($this->modelUrl(config('services.gemini.chat_model')), [
                    'systemInstruction' => [
                        'parts' => [['text' => 'You are MediCare Studio, a helpful writing and operations assistant for a healthcare team. Do not diagnose, prescribe, or make patient-specific treatment decisions. For medical questions, give general educational information and advise consulting a qualified clinician. Keep responses concise and practical.']],
                    ],
                    'contents' => $messages,
                    'generationConfig' => ['maxOutputTokens' => 900],
                ]);

            if ($response->failed()) {
                report(new \RuntimeException('Gemini chat request failed: '.$response->status()));
                return response()->json(['message' => $this->apiErrorMessage($response->status(), 'chat')], $response->status() === 429 ? 429 : 502);
            }

            $content = collect(data_get($response->json(), 'candidates.0.content.parts', []))
                ->pluck('text')
                ->filter()
                ->implode("\n");

            if (blank($content)) {
                return response()->json(['message' => 'The AI service returned an empty response. Please try again.'], 502);
            }

            $conversation = DB::transaction(function () use ($conversation, $user, $validated, $content): UserAiConversation {
                $activeConversation = $conversation ?? $user->aiConversations()->create([
                    'title' => Str::limit(preg_replace('/\s+/', ' ', trim($validated['message'])), 62),
                ]);

                $activeConversation->messages()->create([
                    'role' => 'user',
                    'content' => $validated['message'],
                ]);
                $activeConversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $content,
                ]);
                $activeConversation->touch();

                return $activeConversation;
            });

            return response()->json([
                'reply' => $content,
                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'updated_at' => $conversation->updated_at?->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Unable to reach the AI service. Please try again shortly.'], 502);
        }
    }

    public function image(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1800'],
            'size' => ['nullable', 'in:1024x1024,1536x1024,1024x1536'],
        ]);

        if (blank(config('services.gemini.key'))) {
            return response()->json(['message' => 'AI service is not configured yet. Add GEMINI_API_KEY to the server environment.'], 503);
        }

        try {
            $shape = match ($validated['size'] ?? '1024x1024') {
                '1536x1024' => 'Use a horizontal landscape composition with a 3:2 aspect ratio.',
                '1024x1536' => 'Use a vertical portrait composition with a 2:3 aspect ratio.',
                default => 'Use a balanced square composition.',
            };
            $response = Http::acceptJson()
                ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(120)
                ->post($this->modelUrl(config('services.gemini.image_model')), [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => $validated['prompt'].' '.$shape]],
                    ]],
                    'generationConfig' => ['responseModalities' => ['TEXT', 'IMAGE']],
                ]);

            if ($response->failed()) {
                report(new \RuntimeException('Gemini image request failed: '.$response->status()));
                return response()->json(['message' => $this->apiErrorMessage($response->status(), 'image')], $response->status() === 429 ? 429 : 502);
            }

            $imagePart = collect(data_get($response->json(), 'candidates.0.content.parts', []))
                ->first(fn (array $part) => filled(data_get($part, 'inlineData.data')));
            $base64 = data_get($imagePart, 'inlineData.data');
            if (! is_string($base64) || $base64 === '') {
                return response()->json(['message' => 'The image service returned no image. Please try again.'], 502);
            }

            return response()->json([
                'image' => 'data:'.data_get($imagePart, 'inlineData.mimeType', 'image/png').';base64,'.$base64,
                'filename' => 'medicare-ai-'.Str::lower(Str::random(8)).'.png',
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Unable to reach the image service. Please try again shortly.'], 502);
        }
    }

    private function modelUrl(string $model): string
    {
        return 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';
    }

    private function apiErrorMessage(int $status, string $feature): string
    {
        if ($status === 404) {
            return 'The configured Gemini model is not available for this API key. Update GEMINI_CHAT_MODEL in .env to a model enabled in Google AI Studio, then run php artisan config:clear.';
        }

        if ($status === 429) {
            if ($feature === 'image') {
                return 'Gemini image generation needs available image quota or billing on this API key. Enable it in Google AI Studio, then try again.';
            }

            return 'Gemini has reached a rate or usage limit. Please check the Google AI Studio billing/quota for this API key, then try again.';
        }

        return "The Gemini {$feature} service could not complete that request. Please try again shortly.";
    }
}
