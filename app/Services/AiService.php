<?php

namespace App\Services;

use App\Models\AiMessage;
use App\Models\Book;
use App\Models\Character;
use App\Models\Chapter;
use App\Models\Location;
use App\Models\PlotPoint;
use App\Models\User;
use App\Models\WorldElement;
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

    /**
     * Main ask method - detects if user wants to create something or just chat.
     *
     * @param array|null $chapterIds  Specific chapter IDs to focus on (multi-select support)
     */
    public function ask(User $user, string $message, ?Book $book = null, ?array $chapterIds = null): array
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured. Please set your provider, model, and API key in Settings.');
        }

        // Build context. If specific chapters are selected, focus on them.
        $systemPrompt = $this->buildSystemPrompt($user, $book, true, $chapterIds);

        // Add action instructions
        $systemPrompt .= $this->getActionInstructions();

        $rawResponse = $this->callProvider($provider, $apiKey, $model, $systemPrompt, $message);

        // Try to parse as action JSON
        $action = $this->parseAction($rawResponse);

        if ($action && $book) {
            $result = $this->executeAction($action, $book);
            return [
                'response' => $result['message'],
                'action' => $result['action'],
                'created' => $result['created'] ?? null,
            ];
        }

        return [
            'response' => $rawResponse,
            'action' => null,
            'created' => null,
        ];
    }

    private function getActionInstructions(): string
    {
        return <<<'PROMPT'


IMPORTANT: If the user asks you to CREATE, GENERATE, MAKE, BUAT, BIKIN, TAMBAH, or CIPTAKAN a character, plot point, location, or world element, you MUST respond with ONLY a JSON object in this exact format (no other text before or after):

For creating a character:
{"action":"create_character","data":{"name":"...","role":"protagonist|antagonist|supporting|minor","physical_description":"...","personality_traits":"...","backstory":"...","motivations":"...","notes":"..."}}

For creating a plot point:
{"action":"create_plot","data":{"title":"...","description":"...","act":"beginning|middle|end","status":"planned|in_progress|completed"}}

For creating a location:
{"action":"create_location","data":{"name":"...","type":"city|building|landscape|realm|other","description":"...","atmosphere":"...","notable_features":"..."}}

For creating a world element:
{"action":"create_world","data":{"name":"...","category":"magic system|culture|history|technology|religion|politics|economy","description":"...","rules_laws":"...","notes":"..."}}

Rules for actions:
- Fill in ALL fields based on context from the story/chapters
- Be creative and detailed in descriptions (at least 2-3 sentences each)
- Match the tone and style of the existing story
- For characters mentioned in chapters, extract personality and traits from how they behave in the text
- Only output the JSON, nothing else — no markdown, no explanation before/after

If the user is NOT asking to create something (just asking a question, wanting advice, etc.), respond normally with plain text.
PROMPT;
    }

    private function parseAction(string $response): ?array
    {
        // Try to find JSON in the response
        $response = trim($response);

        // Remove markdown code blocks if present
        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        $decoded = json_decode($response, true);

        if ($decoded && isset($decoded['action']) && isset($decoded['data'])) {
            $validActions = ['create_character', 'create_plot', 'create_location', 'create_world'];
            if (in_array($decoded['action'], $validActions)) {
                return $decoded;
            }
        }

        return null;
    }

    private function executeAction(array $action, Book $book): array
    {
        return match ($action['action']) {
            'create_character' => $this->createCharacter($book, $action['data']),
            'create_plot' => $this->createPlotPoint($book, $action['data']),
            'create_location' => $this->createLocation($book, $action['data']),
            'create_world' => $this->createWorldElement($book, $action['data']),
            default => ['message' => 'Unknown action.', 'action' => null],
        };
    }

    private function createCharacter(Book $book, array $data): array
    {
        $validRoles = ['protagonist', 'antagonist', 'supporting', 'minor'];
        $role = in_array($data['role'] ?? '', $validRoles) ? $data['role'] : 'supporting';

        $character = $book->characters()->create([
            'name' => mb_substr($data['name'] ?? 'Unnamed', 0, 100),
            'role' => $role,
            'physical_description' => mb_substr($data['physical_description'] ?? '', 0, 2000),
            'personality_traits' => mb_substr($data['personality_traits'] ?? '', 0, 2000),
            'backstory' => mb_substr($data['backstory'] ?? '', 0, 5000),
            'motivations' => mb_substr($data['motivations'] ?? '', 0, 2000),
            'notes' => mb_substr($data['notes'] ?? '', 0, 5000),
        ]);

        // Build a short preview for chat feedback
        $preview = $character->personality_traits 
            ? mb_substr($character->personality_traits, 0, 120) 
            : ($character->physical_description ? mb_substr($character->physical_description, 0, 120) : '');

        return [
            'message' => "Karakter \"{$character->name}\" berhasil dibuat sebagai {$role}.",
            'action' => 'create_character',
            'created' => [
                'type' => 'character',
                'id' => $character->id,
                'name' => $character->name,
                'role' => $role,
                'preview' => $preview,
            ],
        ];
    }

    private function createPlotPoint(Book $book, array $data): array
    {
        $validActs = ['beginning', 'middle', 'end'];
        $validStatuses = ['planned', 'in_progress', 'completed'];

        $act = in_array($data['act'] ?? '', $validActs) ? $data['act'] : 'beginning';
        $status = in_array($data['status'] ?? '', $validStatuses) ? $data['status'] : 'planned';
        $nextPosition = $book->plotPoints()->max('position') + 1;

        $plotPoint = $book->plotPoints()->create([
            'title' => mb_substr($data['title'] ?? 'Untitled', 0, 150),
            'description' => mb_substr($data['description'] ?? '', 0, 2000),
            'act' => $act,
            'status' => $status,
            'position' => $nextPosition,
        ]);

        $preview = $plotPoint->description ? mb_substr($plotPoint->description, 0, 110) : '';

        return [
            'message' => "Plot point \"{$plotPoint->title}\" berhasil dibuat di act {$act}.",
            'action' => 'create_plot',
            'created' => [
                'type' => 'plot_point',
                'id' => $plotPoint->id,
                'name' => $plotPoint->title,
                'act' => $act,
                'preview' => $preview,
            ],
        ];
    }

    private function createLocation(Book $book, array $data): array
    {
        $validTypes = ['city', 'building', 'landscape', 'realm', 'other'];
        $type = in_array($data['type'] ?? '', $validTypes) ? $data['type'] : 'other';

        $location = $book->locations()->create([
            'name' => mb_substr($data['name'] ?? 'Unnamed', 0, 200),
            'type' => $type,
            'description' => $data['description'] ?? null,
            'atmosphere' => $data['atmosphere'] ?? null,
            'notable_features' => $data['notable_features'] ?? null,
            'depth' => 0,
        ]);

        return [
            'message' => "Location \"{$location->name}\" ({$type}) created. Check the Locations tab.",
            'action' => 'create_location',
            'created' => ['type' => 'location', 'id' => $location->id, 'name' => $location->name],
        ];
    }

    private function createWorldElement(Book $book, array $data): array
    {
        $element = $book->worldElements()->create([
            'name' => mb_substr($data['name'] ?? 'Unnamed', 0, 150),
            'category' => mb_substr($data['category'] ?? 'other', 0, 50),
            'description' => $data['description'] ?? null,
            'rules_laws' => $data['rules_laws'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return [
            'message' => "World element \"{$element->name}\" ({$element->category}) created. Check the World tab.",
            'action' => 'create_world',
            'created' => ['type' => 'world_element', 'id' => $element->id, 'name' => $element->name],
        ];
    }

    private function buildSystemPrompt(User $user, ?Book $book, bool $includeContent = false, ?array $chapterIds = null): string
    {
        $prompt = "You are a helpful writing assistant for the author \"{$user->name}\". ";
        $prompt .= "You help with their novel writing projects — answering questions, creating characters, building plots, and providing writing advice.\n\n";

        if ($book) {
            $prompt .= "Currently working on: \"{$book->title}\"";
            if ($book->genre) $prompt .= " (Genre: {$book->genre})";
            if ($book->synopsis) $prompt .= "\nSynopsis: {$book->synopsis}";
            $prompt .= "\nStatus: {$book->status->label()}";
            $prompt .= "\nTotal words: " . number_format($book->total_word_count);

            // Add chapters with content
            // If specific chapters are selected, only include those (focused context)
            $chapterQuery = $book->chapters()->orderBy('order_number');

            if (!empty($chapterIds)) {
                $chapterQuery->whereIn('id', $chapterIds);
            }

            $chapters = $chapterQuery->get();

            if ($chapters->isNotEmpty()) {
                $focusNote = !empty($chapterIds) 
                    ? " (FOCUSED on selected chapter(s) only)" 
                    : "";

                $prompt .= "\n\n--- CHAPTERS{$focusNote} ---\n";

                foreach ($chapters as $ch) {
                    $prompt .= "\n## {$ch->title} ({$ch->word_count} words)\n";
                    if ($includeContent && $ch->content_html) {
                        $plainText = strip_tags($ch->content_html);
                        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

                        // When focusing on specific chapters, allow slightly more content per chapter
                        $charLimit = !empty($chapterIds) ? 2200 : 1500;

                        $prompt .= mb_substr($plainText, 0, $charLimit);
                        if (mb_strlen($plainText) > $charLimit) $prompt .= "...[truncated]";
                        $prompt .= "\n";
                    }
                }
            }

            // Add characters
            $characters = $book->characters()->get();
            if ($characters->isNotEmpty()) {
                $prompt .= "\n--- CHARACTERS ---\n";
                foreach ($characters as $char) {
                    $prompt .= "- [ID: {$char->id}] {$char->name}";
                    if ($char->aliases) $prompt .= " (Aliases: {$char->aliases})";
                    $prompt .= " - Role: {$char->role->value}";
                    if ($char->physical_description) $prompt .= " | Look: " . mb_substr($char->physical_description, 0, 100);
                    if ($char->personality_traits) $prompt .= " | Personality: " . mb_substr($char->personality_traits, 0, 200);
                    if ($char->backstory) $prompt .= " | Backstory: " . mb_substr($char->backstory, 0, 200);
                    $prompt .= "\n";
                }
            }

            // Add locations
            $locations = $book->locations()->get();
            if ($locations->isNotEmpty()) {
                $prompt .= "\n--- LOCATIONS ---\n";
                foreach ($locations as $loc) {
                    $prompt .= "- {$loc->name} ({$loc->type->value})";
                    if ($loc->description) $prompt .= ": " . mb_substr($loc->description, 0, 150);
                    $prompt .= "\n";
                }
            }

            // Add plot points
            $plotPoints = $book->plotPoints()->orderBy('position')->get();
            if ($plotPoints->isNotEmpty()) {
                $prompt .= "\n--- PLOT ---\n";
                foreach ($plotPoints as $pp) {
                    $prompt .= "- #{$pp->position} [{$pp->act->value}/{$pp->status->value}] {$pp->title}";
                    if ($pp->description) $prompt .= ": " . mb_substr($pp->description, 0, 150);
                    $prompt .= "\n";
                }
            }

            // Add world elements
            $worldElements = $book->worldElements()->get();
            if ($worldElements->isNotEmpty()) {
                $prompt .= "\n--- WORLD ---\n";
                foreach ($worldElements as $we) {
                    $prompt .= "- [{$we->category}] {$we->name}";
                    if ($we->description) $prompt .= ": " . mb_substr($we->description, 0, 150);
                    $prompt .= "\n";
                }
            }
        } else {
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

        return $prompt;
    }

    private function callProvider(string $provider, string $apiKey, string $model, string $system, string $message): string
    {
        return match ($provider) {
            'anthropic' => $this->callAnthropic($apiKey, $model, $system, $message),
            'google' => $this->callGoogle($apiKey, $model, $system, $message),
            default => $this->callOpenAICompatible($provider, $apiKey, $model, $system, $message),
        };
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
        $model = strtolower(trim($model));
        $model = str_replace('models/', '', $model);
        $model = str_replace(' ', '-', $model);

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

    /**
     * Get recent AI chat history for a user (global).
     */
    public function getRecentHistory(User $user, int $limit = 20): array
    {
        return AiMessage::where('user_id', $user->id)
            ->with('book:id,title')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toISOString(),
                    'book' => $msg->book ? [
                        'id' => $msg->book->id,
                        'title' => $msg->book->title,
                    ] : null,
                    'metadata' => $msg->metadata,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Save a message to AI chat history.
     */
    public function saveMessage(
        User $user,
        string $role,
        string $content,
        ?int $bookId = null,
        ?array $chapterIds = null,
        ?array $metadata = null
    ): AiMessage {
        return AiMessage::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'selected_chapter_ids' => $chapterIds,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    public function generateCharacterRelationships(User $user, Book $book): array
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured.');
        }

        $systemPrompt = $this->buildSystemPrompt($user, $book, true);
        $systemPrompt .= "\n\nYou are a system that analyzes the provided book chapters and characters to determine relationships between characters. Output ONLY a valid JSON array containing objects with keys: 'character_one_id' (int), 'character_two_id' (int), 'type' (string, e.g., 'Friends', 'Enemies', 'Lovers', 'Siblings', 'Allies'). Do not include any explanations, markdown tags, or other text outside the JSON array.";

        $message = "Please analyze the story and characters, and output the relationships JSON array based on the IDs provided in the context.";

        $rawResponse = $this->callProvider($provider, $apiKey, $model, $systemPrompt, $message);

        // Try to parse JSON
        $response = trim($rawResponse);
        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        $decoded = json_decode($response, true);
        if (!$decoded || !is_array($decoded)) {
            throw new \Exception('Invalid JSON from AI.');
        }

        return $decoded;
    }

    public function inlineEdit(User $user, string $text, string $instruction, ?Book $book): string
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured.');
        }

        $systemPrompt = "You are a professional fiction editor. Your task is to modify the given text according to the user's instructions (e.g. rewrite, expand, fix grammar, change tone).\n";
        $systemPrompt .= "OUTPUT ONLY THE MODIFIED TEXT. Do not include explanations, greetings, or markdown formatting around the text. Do not output anything other than the exact replacement text.";

        if ($book) {
            $systemPrompt .= "\nContext: The text belongs to a novel titled '{$book->title}'. Maintain the tone of the story.";
        }

        $message = "Instruction: {$instruction}\n\nText to edit:\n{$text}";

        $response = $this->callProvider($provider, $apiKey, $model, $systemPrompt, $message);

        return trim($response);
    }

    public function betaRead(User $user, Book $book, Chapter $chapter, string $text): array
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured.');
        }

        $systemPrompt = $this->buildSystemPrompt($user, $book, false);
        $systemPrompt .= "\n\nYou are a professional fiction beta reader and editor. The user will provide the text of a chapter titled '{$chapter->title}'. ";
        $systemPrompt .= "You must analyze the chapter and provide critique in specific areas: Pacing, Show Don't Tell, Continuity, and Character Consistency. ";
        $systemPrompt .= "For 'Character Consistency', evaluate if the characters' actions and dialogues in this chapter match their established personality traits and roles from the context. ";
        $systemPrompt .= "Return ONLY a JSON object with keys: 'pacing', 'show_dont_tell', 'continuity', and 'character_consistency'. Each key should contain a string with your detailed feedback. Do not include markdown formatting like ```json or any explanations outside the JSON.";

        $message = "Please beta-read the following chapter text:\n\n" . $text;

        $rawResponse = $this->callProvider($provider, $apiKey, $model, $systemPrompt, $message);

        $response = trim($rawResponse);
        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        $decoded = json_decode($response, true);
        if (!$decoded || !is_array($decoded)) {
            throw new \Exception('Invalid JSON from AI.');
        }

        return $decoded;
    }

    public function extractCharacters(User $user, Book $book, Chapter $chapter): int
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured.');
        }

        $systemPrompt = $this->buildSystemPrompt($user, $book, false);
        $systemPrompt .= "\n\nYou are an AI assistant that extracts NEW characters from a chapter. ";
        $systemPrompt .= "The user will provide the text of the chapter. Compare the characters mentioned in the text with the existing characters list from the context. ";
        $systemPrompt .= "If you find any important characters in the text that DO NOT exist in the context yet, output them. ";
        $systemPrompt .= "CRITICAL RULE: ONLY extract characters that have an explicit PROPER NAME (e.g., 'John', 'Murayama Miu', 'Siti'). DO NOT extract generic descriptions, pronouns, or job titles as characters (e.g., ignore 'Gadis berjaket kulit', 'the waiter', 'old man', 'the king'). If a character's real name is not mentioned, ignore them completely. ";
        $systemPrompt .= "Return ONLY a valid JSON array of objects. Each object MUST have these keys: 'name' (string), 'role' (string: 'supporting' or 'minor'), 'physical_description' (string, inferred from text or empty), and 'personality_traits' (string, inferred from text or empty). ";
        $systemPrompt .= "If no new characters are found, return an empty array `[]`. Do NOT include markdown like ```json or any other text.";

        $message = "Here is the chapter text. Extract any NEW characters not in the current character list:\n\n" . strip_tags($chapter->content_html ?? '');

        $rawResponse = $this->callProvider($provider, $apiKey, $model, $systemPrompt, $message);

        $response = trim($rawResponse);
        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new \Exception('Invalid JSON from AI.');
        }

        $createdCount = 0;
        foreach ($decoded as $charData) {
            if (empty($charData['name'])) continue;
            
            // Double check if name already exists (case-insensitive) to prevent duplicates
            $exists = $book->characters()->whereRaw('LOWER(name) = ?', [strtolower($charData['name'])])->exists();
            if (!$exists) {
                $book->characters()->create([
                    'name' => mb_substr($charData['name'], 0, 100),
                    'role' => in_array($charData['role'] ?? '', ['supporting', 'minor']) ? $charData['role'] : 'supporting',
                    'physical_description' => mb_substr($charData['physical_description'] ?? '', 0, 2000),
                    'personality_traits' => mb_substr($charData['personality_traits'] ?? '', 0, 2000),
                ]);
                $createdCount++;
            }
        }

        return $createdCount;
    }

    public function aiWizard(User $user, Book $book, string $premise, string $framework): array
    {
        $provider = $user->ai_provider;
        $model = $user->ai_model;
        $apiKey = $user->ai_api_key;

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception('AI not configured.');
        }

        $systemPrompt = "You are a professional narrative designer and plot architect. The user is writing a novel titled '{$book->title}'. ";
        $systemPrompt .= "You must generate a structured plot outline using the '{$framework}' framework, based on their premise. ";
        $systemPrompt .= "Return ONLY a valid JSON array of objects. Each object MUST have these keys: 'title' (string, max 100 chars), 'description' (string), and 'act' (string: exactly 'beginning', 'middle', or 'end'). ";
        $systemPrompt .= "Do NOT include markdown like ```json or any conversational text. Just the raw JSON array. Keep the descriptions concise but evocative.";

        $message = "Premise:\n" . $premise;

        $rawResponse = $this->callProvider($provider, $apiKey, $model, $systemPrompt, $message);

        $response = trim($rawResponse);
        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        $decoded = json_decode($response, true);
        if (!$decoded || !is_array($decoded)) {
            throw new \Exception('Invalid JSON from AI.');
        }

        return $decoded;
    }
}
