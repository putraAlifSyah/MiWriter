# MiWriter — Write Smarter with AI

MiWriter is a novel writing platform with a built-in AI assistant that understands your entire story. It knows your characters, plot, locations, and world — so you can ask it anything while you write.

## AI Ask — Your Context-Aware Writing Partner

The AI assistant lives in every page as a floating chat. Select a book, and it instantly has full context of:

- All your characters (names, roles, backstories)
- Your plot outline (every plot point, act, status)
- Locations and their hierarchy
- World-building elements (magic systems, cultures, rules)
- Chapter structure and word counts

Ask things like:
- "What's the motivation of my antagonist?"
- "Suggest a plot twist for act 2"
- "Does my timeline have any inconsistencies?"
- "Help me describe the atmosphere of the capital city"
- "What would happen if these two characters met?"

You bring your own API key. Supported providers:

**🔐 Security Guarantee:** Your API key is stored securely in your own local database. MiWriter does not store, transmit, or share your API key with any third party other than the AI provider you select. We have zero access to your keys or your story content.

| Provider | Example Models |
|----------|---------------|
| OpenAI | gpt-4o, gpt-4-turbo, gpt-3.5-turbo |
| Anthropic | claude-sonnet-4-20250514, claude-3-haiku |
| Google | gemini-3.5-flash, gemini-3.1-flash-lite |
| Groq | llama-3.3-70b-versatile, mixtral-8x7b |
| OpenRouter | any of 100+ models |

Setup takes 30 seconds: go to Settings, pick a provider, paste your key, done.

## Core Features

**Writing**
- Rich text editor (Quill.js) with bold, italic, headings, lists, blockquotes
- Auto-save 2 seconds after you stop typing
- Real-time word count
- **Focus Mode**: Distraction-free writing mode that dims inactive paragraphs
- **Chapter Snapshots**: Save and restore version history for every chapter
- Offline backup to browser storage

**Organization**
- Multiple books with cover images
- Chapters with drag-and-drop reordering
- Character profiles with photo references
- **Character Relationship Map**: Visual graph of character connections with optional AI Auto-Detect
- Location builder with 5-level hierarchy
- **Plot Kanban Board**: Visual drag-and-drop board for your narrative arc (Act 1 / 2 / 3)
- World-building entries grouped by category

**Productivity & AI**
- Daily and weekly writing targets
- **Advanced Goal Tracker (NaNoWriMo Mode)**: Set a target word count and deadline for your book, track daily required pace.
- Streak tracking and statistics (average daily words, completion date)
- **Inline AI Editor**: Select text in the editor to instantly rewrite, expand, or fix grammar using AI
- **AI Beta Reader & Continuity Checker**: Get professional AI feedback on your chapter's pacing, "show don't tell", and story continuity.
- **AI Plot Framework Wizard**: Auto-generate story frameworks (Save The Cat, Hero's Journey, 3-Act) directly into your Kanban board.
- Floating AI chat assistant available on every page

**Export**
- Plain text (.txt)
- Markdown (.md)
- **eBook (.epub)** for standard digital publishing
- **PDF (.pdf)** for printing and sharing
- Per-chapter or full book export

**Other**
- Dark mode
- Bilingual interface (English / Bahasa Indonesia)
- Responsive (desktop + mobile)
- Full-text search across all book content

## Tech Stack

- PHP 8.1+ / Laravel
- MySQL
- Vanilla JavaScript + Quill.js
- Multi-provider AI via REST (no server-side AI costs — users bring their own key)

## Getting Started

```bash
git clone https://github.com/putraAlifSyah/MiWriter.git
cd MiWriter
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` with your MySQL credentials, then:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000, register an account, and start writing.

To enable AI: go to Settings > AI Assistant, select your provider, enter your model and API key.

## Screenshots

Dashboard with writing stats and book grid. AI chat accessible from the floating button on every page.

## License

MIT
