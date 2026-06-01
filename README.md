# MiWriter

A full-stack novel writing platform built with PHP/Laravel, MySQL, and vanilla JavaScript. Provides authors with tools for writing, organizing, and managing novels — including a rich text editor (Quill.js), character/location builders, plot outlining, writing targets, statistics tracking, world-building tools, and data export.

## Features

- 📝 Rich text editor with auto-save (debounce 2s)
- 📚 Book management with cover image upload
- 👤 Character builder with photo upload & relationship mapping
- 🗺️ Location hierarchy builder
- 📋 Plot timeline with drag-and-drop
- 🎯 Daily/weekly writing targets & progress tracking
- 📊 Writing statistics, streaks, and heatmap
- 🌍 World-building tools with cross-references
- 🔍 Full-text search across all content
- 📤 Export to TXT/Markdown
- 🌙 Dark mode

## Tech Stack

- **Backend:** PHP 8.1+ / Laravel
- **Database:** MySQL
- **Frontend:** Vanilla JavaScript + Quill.js
- **Design:** Treto theme (Jost font, #fa4729 accent)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

## License

MIT
