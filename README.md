# MiWriter

MiWriter is a full-stack novel writing platform built with Laravel and vanilla JavaScript. It helps authors write, organize, and manage their novels with a clean distraction-free interface.

## Features

- Rich text editor with real-time auto-save
- Book management with cover images
- Character builder with photo references and relationship mapping
- Location hierarchy builder
- Plot timeline with drag-and-drop ordering
- Daily and weekly writing targets with progress tracking
- Writing statistics including streaks, word count history, and heatmap
- World-building tools with cross-references between elements
- Full-text search across all book content
- Export to plain text or Markdown
- Dark mode support
- Responsive design for desktop and mobile

## Tech Stack

- Backend: PHP 8.1+ / Laravel
- Database: MySQL
- Frontend: Vanilla JavaScript, Quill.js editor
- Design: Custom CSS with Jost font

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Configure your `.env` file with your MySQL credentials before running migrations.

## License

MIT
