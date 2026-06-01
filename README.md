# MiWriter

A novel writing platform with built-in AI assistant. Write, organize, and manage your novels with intelligent help that understands your characters, plot, and world.

## AI-Powered Writing Assistant

MiWriter includes a context-aware AI chat that knows your entire book — characters, locations, plot points, world-building elements, and chapter structure. Ask it anything about your story, get suggestions, brainstorm ideas, or check for consistency.

**Bring your own API key.** Choose from multiple providers:
- OpenAI (GPT-4o, GPT-4, GPT-3.5)
- Anthropic (Claude 3.5, Claude 3)
- Google (Gemini Pro, Gemini Flash)
- Groq (Llama, Mixtral)
- OpenRouter (access to 100+ models)

The AI assistant is accessible from any page via the floating chat button. Select a specific book for focused context, or ask general questions across all your projects.

## Features

- Rich text editor with real-time auto-save (2s debounce)
- Book management with cover images
- Character builder with photo references and relationship mapping
- Location hierarchy builder (up to 5 levels)
- Plot timeline with sequential ordering
- Daily and weekly writing targets with progress tracking
- Writing statistics: streaks, word count history, estimated completion
- World-building tools with categories and cross-references
- Full-text search across all book content
- Export to plain text or Markdown
- Dark mode
- Bilingual interface (English / Bahasa Indonesia)
- Responsive design for desktop and mobile

## Tech Stack

- Backend: PHP 8.1+ / Laravel
- Database: MySQL
- Frontend: Vanilla JavaScript, Quill.js editor
- AI: Multi-provider support via REST API (user provides their own key)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Configure your `.env` file with MySQL credentials before running migrations.

After registering, go to Settings to configure your AI provider and API key.

## License

MIT
