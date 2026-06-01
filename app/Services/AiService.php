<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AiService
{
    private array $providerEndpoints = [
        'openai' => 'https://api.openai.com/v1/chat/completions',
        'anthropic' => 'https://api.anthropic.com/v1/messages',
        'google' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
        'groq' => 'https://api.groq.com/openai/v1/chat/completions',
        'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
    ];

    public function ask(User $user, string $message, ?Book $book = null): string
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured. Please set your provider, model, and API key in Settings.');
        }

        // Build context from user's book data
        $systemPrompt = $this->buildSystemPrompt($user, $book);

        return match ($provider) {
            'anthropic' => $this->callAnthropic($apiKey, $model, $systemPrompt, $message),
            'google' => $this->callGoogle($apiKey, $model, $systemPrompt, $message),
            default => $this->callOpenAICompatible($provider, $apiKey, $model, $systemPrompt, $message),
        };
    }

    private function buildSystemPrompt(User $user, ?Book $book): string
    {
        $prompt = "You are a helpful writing assistant for the author \"{$user->name}\". ";
        $prompt .= "You help with their novel writing projects — answering questions about their characters, plot, locations, world-building, and providing writing advice.\n\n";

        if ($book) {
            $prompt .= "Currently working on: \"{$book->title}\"";
            if ($book->genre) $prompt .= " (Genre: {$book->genre})";
            if ($book->synopsis) $prompt .= "\nSynopsis: {$book->synopsis}";
            $prompt .= "\nStatus: {$book->status->label()}";
            $prompt .= "\nTotal words: " . number_format($book->total_word_count);

            // Add chapters info
            $chapters = $book->chapters()->orderBy('order_number')->get(['title', 'word_count']);
            if ($chapters->isNotEmpty()) {
                $prompt .= "\n\nChapters:\n";
                foreach ($chapters as $ch) {
                    $prompt .= "- {$ch->title} ({$ch->word_count} words)\n";
                }
            }

            // Add characters
            $characters = $book->characters()->get(['name', 'role', 'backstory']);
            if ($characters->isNotEmpty()) {
                $prompt .= "\nCharacters:\n";
                foreach ($characters as $char) {
                    $prompt .= "- {$char->name} ({$char->role->value})";
                    if ($char->backstory) $prompt .= ": " . mb_substr($char->backstory, 0, 200);
                    $prompt .= "\n";
                }
            }

            // Add locations
            $locations = $book->locations()->get(['name', 'type', 'description']);
            if ($locations->isNotEmpty()) {
                $prompt .= "\nLocations:\n";
                foreach ($locations as $loc) {
                    $prompt .= "- {$loc->name} ({$loc->type->value})";
                    if ($loc->description) $prompt .= ": " . mb_substr($loc->description, 0, 150);
                    $prompt .= "\n";
                }
            }

            // Add plot points
            $plotPoints = $book->plotPoints()->orderBy('position')->get(['title', 'act', 'status', 'description']);
            if ($plotPoints->isNotEmpty()) {
                $prompt .= "\nPlot Points:\n";
                foreach ($plotPoints as $pp) {
                    $prompt .= "- [{$pp->act->value}/{$pp->status->value}] {$pp->title}";
                    if ($pp->description) $prompt .= ": " . mb_substr($pp->description, 0, 150);
                    $prompt .= "\n";
                }
            }

            // Add world elements
            $worldElements = $book->worldElements()->get(['name', 'category', 'description']);
            if ($worldElements->isNotEmpty()) {
                $prompt .= "\nWorld Elements:\n";
                foreach ($worldElements as $we) {
                    $prompt .= "- [{$we->category}] {$we->name}";
                    if ($we->description) $prompt .= ": " . mb_substr($we->description, 0, 150);
                    $prompt .= "\n";
                }
            }
        } else {
            // No specific book — give overview of all books
            $books = $user->books()->get(['title', 'genre', 'status']);
            if ($books->isNotEmpty()) {
                $prompt .= "The author's books:\n";
                foreach ($books as $b) {
                    $prompt .= "- \"{$b->title}\" ({$b->status->label()})";
                    if ($b->genre) $prompt .= " - {$b->genre}";
                    $prompt .= "\n";
                }
            }
        }

        $prompt .= "\nRespond concisely and helpfully. If asked about specific content you don't have details on, say so honestly.";

        return $prompt;
    }

    private function callOpenAICompatible(string $provider, string $apiKey, string $model, string $system, string $message): string
    {
        $endpoint = $this->providerEndpoints[$provider] ?? $this->providerEndpoints['openai'];

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("AI request failed: {$error}");
        }

        return $response->json('choices.0.message.content') ?? 'No response received.';
    }

    private function callAnthropic(string $apiKey, string $model, string $system, string $message): string
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post($this->providerEndpoints['anthropic'], [
                'model' => $model,
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 2000,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("AI request failed: {$error}");
        }

        return $response->json('content.0.text') ?? 'No response received.';
    }

    private function callGoogle(string $apiKey, string $model, string $system, string $message): string
    {
        $endpoint = str_replace('{model}', $model, $this->providerEndpoints['google']);
        $endpoint .= "?key={$apiKey}";

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => [
                    ['parts' => [['text' => $message]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 2000,
                    'temperature' => 0.7,
                ],
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("AI request failed: {$error}");
        }

        return $response->json('candidates.0.content.parts.0.text') ?? 'No response received.';
    }
}
