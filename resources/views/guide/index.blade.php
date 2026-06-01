@extends('layouts.app')

@push('styles')
<style>
    .guide-section { margin-bottom: 32px; }
    .guide-section h2 { font-size: var(--font-size-lg); font-weight: 700; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--color-border-light); }
    .guide-section h3 { font-size: var(--font-size-base); font-weight: 600; margin: 16px 0 8px; }
    .guide-section p, .guide-section li { font-size: var(--font-size-sm); line-height: 1.7; color: var(--color-text-secondary); }
    .guide-section ul { padding-left: 20px; margin: 8px 0; }
    .guide-section li { margin-bottom: 6px; }
    .guide-section code { background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px; font-size: var(--font-size-xs); }
    .guide-toc { position: sticky; top: 20px; }
    .guide-toc a { display: block; padding: 6px 12px; font-size: var(--font-size-sm); color: var(--color-text-muted); border-radius: var(--radius-sm); transition: all var(--transition); }
    .guide-toc a:hover { background: var(--color-accent-light); color: var(--color-accent); }
    .lang-toggle { display: inline-flex; border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; }
    .lang-toggle button { padding: 6px 14px; font-size: var(--font-size-sm); font-weight: 500; border: none; background: transparent; cursor: pointer; color: var(--color-text-muted); transition: all var(--transition); font-family: var(--font-family); }
    .lang-toggle button.active { background: var(--color-accent); color: #fff; }
</style>
@endpush

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h1 class="nwp-heading" style="font-size:var(--font-size-2xl);">
            <span class="lang-en">User Guide</span>
            <span class="lang-id">Panduan Pengguna</span>
        </h1>
        <p class="nwp-text-sm nwp-text-muted" style="margin-top:4px;">
            <span class="lang-en">Everything you need to know about MiWriter.</span>
            <span class="lang-id">Semua yang perlu kamu tahu tentang MiWriter.</span>
        </p>
    </div>
    <div class="lang-toggle">
        <button onclick="setLang('en')" id="lang-btn-en" class="active">EN</button>
        <button onclick="setLang('id')" id="lang-btn-id">ID</button>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 240px; gap:32px;">
    <!-- Main Content -->
    <div id="guide-content">

        <!-- Getting Started -->
        <div class="guide-section" id="getting-started">
            <div class="lang-en">
                <h2>Getting Started</h2>
                <p>MiWriter is a writing platform designed for novelists. After registering and logging in, you'll land on the <strong>Dashboard</strong> which shows an overview of all your writing activity.</p>
                <h3>Quick Start</h3>
                <ul>
                    <li>Click <strong>"+ New Book"</strong> to create your first project</li>
                    <li>Give it a title and you'll be taken to the book workspace</li>
                    <li>Click <strong>"+ Add Chapter"</strong> to start writing</li>
                    <li>The editor auto-saves 2 seconds after you stop typing</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Memulai</h2>
                <p>MiWriter adalah platform menulis yang dirancang untuk novelis. Setelah mendaftar dan login, kamu akan masuk ke <strong>Dashboard</strong> yang menampilkan ringkasan semua aktivitas menulismu.</p>
                <h3>Langkah Cepat</h3>
                <ul>
                    <li>Klik <strong>"+ New Book"</strong> untuk membuat proyek pertama</li>
                    <li>Beri judul, lalu kamu akan masuk ke workspace buku</li>
                    <li>Klik <strong>"+ Add Chapter"</strong> untuk mulai menulis</li>
                    <li>Editor otomatis menyimpan 2 detik setelah kamu berhenti mengetik</li>
                </ul>
            </div>
        </div>

        <!-- Dashboard -->
        <div class="guide-section" id="dashboard">
            <div class="lang-en">
                <h2>Dashboard</h2>
                <p>The dashboard gives you a bird's-eye view of your writing life:</p>
                <ul>
                    <li><strong>Stats cards</strong> — Total words, books, chapters, characters, current streak, and words written today</li>
                    <li><strong>Writing progress</strong> — Daily and weekly target progress bars (set targets from the Statistics page)</li>
                    <li><strong>Last 7 days chart</strong> — Visual bar chart of your daily word output</li>
                    <li><strong>Your books</strong> — Quick access to all your book projects</li>
                    <li><strong>Recent activity</strong> — Last 8 items you edited across all books</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Dashboard</h2>
                <p>Dashboard memberikan gambaran menyeluruh tentang aktivitas menulismu:</p>
                <ul>
                    <li><strong>Kartu statistik</strong> — Total kata, buku, chapter, karakter, streak saat ini, dan kata hari ini</li>
                    <li><strong>Progress menulis</strong> — Progress bar target harian dan mingguan (set target dari halaman Statistics)</li>
                    <li><strong>Grafik 7 hari terakhir</strong> — Bar chart visual output kata harianmu</li>
                    <li><strong>Buku-bukumu</strong> — Akses cepat ke semua proyek buku</li>
                    <li><strong>Aktivitas terbaru</strong> — 8 item terakhir yang kamu edit di semua buku</li>
                </ul>
            </div>
        </div>

        <!-- Book Management -->
        <div class="guide-section" id="books">
            <div class="lang-en">
                <h2>Book Management</h2>
                <p>Each book is a separate project with its own chapters, characters, locations, plot, and world elements.</p>
                <h3>Creating a Book</h3>
                <p>Click "+ New Book" from the dashboard or sidebar. Enter a title (1-200 characters) and the book is created with "Draft" status.</p>
                <h3>Book Workspace</h3>
                <p>The book workspace has tabs for: <strong>Chapters, Characters, Locations, Plot, World, Statistics</strong>. You can also edit the book's metadata (title, genre, synopsis, status) and upload a cover image.</p>
                <h3>Cover Image</h3>
                <ul>
                    <li>Click the cover area to upload an image</li>
                    <li>Accepted formats: JPEG, PNG, WebP</li>
                    <li>Minimum dimensions: 600x900 pixels</li>
                    <li>Maximum file size: 5MB</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Manajemen Buku</h2>
                <p>Setiap buku adalah proyek terpisah dengan chapter, karakter, lokasi, plot, dan elemen dunia sendiri.</p>
                <h3>Membuat Buku</h3>
                <p>Klik "+ New Book" dari dashboard atau sidebar. Masukkan judul (1-200 karakter) dan buku dibuat dengan status "Draft".</p>
                <h3>Workspace Buku</h3>
                <p>Workspace buku punya tab: <strong>Chapters, Characters, Locations, Plot, World, Statistics</strong>. Kamu juga bisa edit metadata buku (judul, genre, sinopsis, status) dan upload cover.</p>
                <h3>Cover Image</h3>
                <ul>
                    <li>Klik area cover untuk upload gambar</li>
                    <li>Format yang diterima: JPEG, PNG, WebP</li>
                    <li>Dimensi minimum: 600x900 piksel</li>
                    <li>Ukuran file maksimum: 5MB</li>
                </ul>
            </div>
        </div>

        <!-- Editor -->
        <div class="guide-section" id="editor">
            <div class="lang-en">
                <h2>Writing Editor</h2>
                <p>The editor uses Quill.js and supports rich text formatting.</p>
                <h3>Features</h3>
                <ul>
                    <li><strong>Auto-save</strong> — Saves automatically 2 seconds after you stop typing. Status shown at top right.</li>
                    <li><strong>Manual save</strong> — Press <code>Ctrl+S</code> (or <code>Cmd+S</code> on Mac)</li>
                    <li><strong>Formatting</strong> — Bold, italic, underline, strikethrough, headings (H1-H3), blockquote, ordered/unordered lists</li>
                    <li><strong>Word count</strong> — Real-time word count displayed at the bottom</li>
                    <li><strong>Fullscreen mode</strong> — Click "Fullscreen" button for distraction-free writing</li>
                    <li><strong>AI Beta Reader</strong> — Click "🤖 Beta Reader" to get AI feedback on pacing, show don't tell, and continuity.</li>
                    <li><strong>Offline protection</strong> — If save fails, content is backed up to browser storage and restored on next visit</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Editor Menulis</h2>
                <p>Editor menggunakan Quill.js dan mendukung format teks kaya.</p>
                <h3>Fitur</h3>
                <ul>
                    <li><strong>Auto-save</strong> — Menyimpan otomatis 2 detik setelah berhenti mengetik. Status ditampilkan di kanan atas.</li>
                    <li><strong>Simpan manual</strong> — Tekan <code>Ctrl+S</code> (atau <code>Cmd+S</code> di Mac)</li>
                    <li><strong>Formatting</strong> — Bold, italic, underline, strikethrough, heading (H1-H3), blockquote, ordered/unordered list</li>
                    <li><strong>Jumlah kata</strong> — Hitungan kata real-time ditampilkan di bawah</li>
                    <li><strong>Mode fullscreen</strong> — Klik tombol "Fullscreen" untuk menulis tanpa gangguan</li>
                    <li><strong>AI Beta Reader</strong> — Klik "🤖 Beta Reader" untuk mendapat kritik AI tentang tempo (pacing), show don't tell, dan kontinuitas cerita.</li>
                    <li><strong>Proteksi offline</strong> — Jika gagal simpan, konten di-backup ke browser storage dan di-restore saat kunjungan berikutnya</li>
                </ul>
            </div>
        </div>

        <!-- Characters -->
        <div class="guide-section" id="characters">
            <div class="lang-en">
                <h2>Character Builder</h2>
                <p>Create detailed profiles for your story's characters.</p>
                <h3>Creating a Character</h3>
                <ul>
                    <li>Go to the Characters tab in your book</li>
                    <li>Click "+ Add Character"</li>
                    <li>Fill in: name (required), aliases (nicknames), role, physical description, personality, backstory, notes</li>
                </ul>
                <h3>Character Statistics & Auto-Detect</h3>
                <ul>
                    <li><strong>Statistics</strong>: View how many times a character is mentioned (using their name or aliases) and in which chapters they appear on their detail page.</li>
                    <li><strong>Auto-Detect</strong>: In the chapter editor, click "✨ Auto-Detect" in the side panel to let AI scan your text and automatically create profiles for any new characters you've written.</li>
                </ul>
                <h3>Search & Filter</h3>
                <p>Use the search bar to find characters by name. Use the role dropdown to filter by character type.</p>
            </div>
            <div class="lang-id">
                <h2>Pembangun Karakter</h2>
                <p>Buat profil detail untuk karakter-karakter ceritamu.</p>
                <h3>Membuat Karakter</h3>
                <ul>
                    <li>Buka tab Characters di bukumu</li>
                    <li>Klik "+ Add Character"</li>
                    <li>Isi: nama (wajib), aliases (nama panggilan), peran, deskripsi fisik, kepribadian, latar belakang, catatan</li>
                </ul>
                <h3>Statistik Karakter & Auto-Detect</h3>
                <ul>
                    <li><strong>Statistik</strong>: Lihat berapa kali karakter disebut (menggunakan nama atau panggilannya) dan di bab mana saja mereka muncul pada halaman detail mereka.</li>
                    <li><strong>Auto-Detect</strong>: Di editor bab, klik "✨ Auto-Detect" di panel samping agar AI memindai teksmu dan otomatis membuat profil untuk karakter baru yang kamu tulis.</li>
                </ul>
                <h3>Cari & Filter</h3>
                <p>Gunakan kolom pencarian untuk mencari karakter berdasarkan nama. Gunakan dropdown peran untuk filter berdasarkan tipe.</p>
            </div>
        </div>

        <!-- Locations -->
        <div class="guide-section" id="locations">
            <div class="lang-en">
                <h2>Location Builder</h2>
                <p>Document the places and settings in your story.</p>
                <ul>
                    <li><strong>Types:</strong> City, Building, Landscape, Realm, Other</li>
                    <li><strong>Fields:</strong> Name, type, description, atmosphere, notable features, notes</li>
                    <li><strong>Hierarchy:</strong> Locations can be nested (e.g., a room inside a building inside a city) up to 5 levels deep</li>
                    <li><strong>Search:</strong> Search by name, type, or description</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Pembangun Lokasi</h2>
                <p>Dokumentasikan tempat dan setting dalam ceritamu.</p>
                <ul>
                    <li><strong>Tipe:</strong> City, Building, Landscape, Realm, Other</li>
                    <li><strong>Field:</strong> Nama, tipe, deskripsi, atmosfer, fitur penting, catatan</li>
                    <li><strong>Hierarki:</strong> Lokasi bisa bersarang (misal: ruangan di dalam gedung di dalam kota) hingga 5 level</li>
                    <li><strong>Pencarian:</strong> Cari berdasarkan nama, tipe, atau deskripsi</li>
                </ul>
            </div>
        </div>

        <!-- Plot -->
        <div class="guide-section" id="plot">
            <div class="lang-en">
                <h2>Plot Outline</h2>
                <p>Plan your story's narrative arc with structured plot points.</p>
                <h3>Creating Plot Points</h3>
                <ul>
                    <li>Click "+ Add Plot Point"</li>
                    <li>Set a title, choose the act (Beginning/Middle/End), set status (Planned/In Progress/Completed)</li>
                    <li>Add a description to detail what happens</li>
                    <li>Plot points are automatically numbered in sequence</li>
                </ul>
                <h3>AI Plot Wizard</h3>
                <p>Click "✨ AI Plot Wizard" to automatically generate a structured outline (Save The Cat, Hero's Journey, 3-Act) based on a short premise.</p>
                <h3>Linking</h3>
                <p>You can link up to 20 characters and 10 locations to each plot point (via API).</p>
            </div>
            <div class="lang-id">
                <h2>Outline Plot</h2>
                <p>Rencanakan alur naratif ceritamu dengan plot point terstruktur.</p>
                <h3>Membuat Plot Point</h3>
                <ul>
                    <li>Klik "+ Add Plot Point"</li>
                    <li>Set judul, pilih babak (Beginning/Middle/End), set status (Planned/In Progress/Completed)</li>
                    <li>Tambahkan deskripsi untuk menjelaskan apa yang terjadi</li>
                    <li>Plot point otomatis diberi nomor urut</li>
                </ul>
                <h3>AI Plot Wizard</h3>
                <p>Klik "✨ AI Plot Wizard" untuk secara otomatis membuat kerangka terstruktur (Save The Cat, Hero's Journey, 3-Act) berdasarkan premis singkat.</p>
                <h3>Menghubungkan</h3>
                <p>Kamu bisa menghubungkan hingga 20 karakter dan 10 lokasi ke setiap plot point (via API).</p>
            </div>
        </div>

        <!-- World Building -->
        <div class="guide-section" id="world">
            <div class="lang-en">
                <h2>World Building</h2>
                <p>Document the rules, cultures, and systems of your fictional world.</p>
                <h3>Categories</h3>
                <p>Elements are organized by category: Magic System, Culture, History, Technology, Religion, Politics, Economy, or custom categories.</p>
                <h3>Fields</h3>
                <ul>
                    <li><strong>Name</strong> — Required, up to 150 characters</li>
                    <li><strong>Description</strong> — Up to 10,000 characters</li>
                    <li><strong>Rules/Laws</strong> — Document the rules of this element (up to 5,000 characters)</li>
                    <li><strong>Notes</strong> — Additional notes (up to 5,000 characters)</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>World Building</h2>
                <p>Dokumentasikan aturan, budaya, dan sistem dunia fiksimu.</p>
                <h3>Kategori</h3>
                <p>Elemen diorganisir berdasarkan kategori: Magic System, Culture, History, Technology, Religion, Politics, Economy, atau kategori kustom.</p>
                <h3>Field</h3>
                <ul>
                    <li><strong>Nama</strong> — Wajib, maksimal 150 karakter</li>
                    <li><strong>Deskripsi</strong> — Maksimal 10.000 karakter</li>
                    <li><strong>Aturan/Hukum</strong> — Dokumentasikan aturan elemen ini (maks 5.000 karakter)</li>
                    <li><strong>Catatan</strong> — Catatan tambahan (maks 5.000 karakter)</li>
                </ul>
            </div>
        </div>

        <!-- Statistics -->
        <div class="guide-section" id="statistics">
            <div class="lang-en">
                <h2>Statistics & Writing Targets</h2>
                <p>Track your writing habits and set goals to stay consistent.</p>
                <h3>Writing Targets</h3>
                <ul>
                    <li><strong>Daily target</strong> — Set a word count goal per day (1 - 100,000 words)</li>
                    <li><strong>Weekly target</strong> — Set a word count goal per week (1 - 500,000 words)</li>
                    <li><strong>NaNoWriMo Mode (Project Goal)</strong> — Set a target word count and a strict deadline to track your required daily pace.</li>
                </ul>
                <h3>Stats Tracked</h3>
                <ul>
                    <li><strong>Current streak</strong> — Consecutive days meeting your daily target</li>
                    <li><strong>Longest streak</strong> — Your all-time best streak</li>
                    <li><strong>Average daily</strong> — Average words per day over the last 30 days</li>
                    <li><strong>Estimated completion</strong> — Based on remaining words and your average pace</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Statistik & Target Menulis</h2>
                <p>Lacak kebiasaan menulismu dan set target agar tetap konsisten.</p>
                <h3>Target Menulis</h3>
                <ul>
                    <li><strong>Target harian</strong> — Set target jumlah kata per hari (1 - 100.000 kata)</li>
                    <li><strong>Target mingguan</strong> — Set target jumlah kata per minggu (1 - 500.000 kata)</li>
                    <li><strong>NaNoWriMo Mode (Project Goal)</strong> — Set target total kata dan tenggat waktu (deadline) untuk melacak kecepatan harian yang dibutuhkan.</li>
                </ul>
                <h3>Statistik yang Dilacak</h3>
                <ul>
                    <li><strong>Streak saat ini</strong> — Hari berturut-turut memenuhi target harian</li>
                    <li><strong>Streak terpanjang</strong> — Rekor streak terbaikmu</li>
                    <li><strong>Rata-rata harian</strong> — Rata-rata kata per hari selama 30 hari terakhir</li>
                    <li><strong>Estimasi selesai</strong> — Berdasarkan sisa kata dan kecepatan rata-ratamu</li>
                </ul>
            </div>
        </div>

        <!-- Export -->
        <div class="guide-section" id="export">
            <div class="lang-en">
                <h2>Export</h2>
                <p>Export your book or individual chapters as files.</p>
                <ul>
                    <li><strong>Plain Text (.txt)</strong> — All formatting stripped, just the text</li>
                    <li><strong>Markdown (.md)</strong> — Formatting converted to Markdown syntax (bold, italic, headings, lists)</li>
                    <li><strong>eBook (.epub)</strong> — Standard industry format for digital publishing.</li>
                    <li><strong>PDF (.pdf)</strong> — Beautifully rendered document ready for print.</li>
                </ul>
                <p>Access export from the book workspace via the "Export" button.</p>
            </div>
            <div class="lang-id">
                <h2>Ekspor</h2>
                <p>Ekspor buku atau chapter individual sebagai file.</p>
                <ul>
                    <li><strong>Plain Text (.txt)</strong> — Semua format dihapus, hanya teks</li>
                    <li><strong>Markdown (.md)</strong> — Format dikonversi ke sintaks Markdown (bold, italic, heading, list)</li>
                    <li><strong>eBook (.epub)</strong> — Format standar industri untuk penerbitan digital.</li>
                    <li><strong>PDF (.pdf)</strong> — Dokumen yang di-render cantik dan siap cetak.</li>
                </ul>
                <p>Akses ekspor dari workspace buku via tombol "Export".</p>
            </div>
        </div>

        <!-- Dark Mode -->
        <div class="guide-section" id="darkmode">
            <div class="lang-en">
                <h2>Dark Mode</h2>
                <p>Toggle dark mode from the sidebar (bottom left). Your preference is saved and persists across sessions.</p>
            </div>
            <div class="lang-id">
                <h2>Mode Gelap</h2>
                <p>Toggle mode gelap dari sidebar (kiri bawah). Preferensimu disimpan dan bertahan antar sesi.</p>
            </div>
        </div>

        <!-- Keyboard Shortcuts -->
        <div class="guide-section" id="shortcuts">
            <div class="lang-en">
                <h2>Keyboard Shortcuts</h2>
                <ul>
                    <li><code>Ctrl+S</code> / <code>Cmd+S</code> — Save current chapter</li>
                    <li><code>Ctrl+B</code> — Bold</li>
                    <li><code>Ctrl+I</code> — Italic</li>
                    <li><code>Ctrl+U</code> — Underline</li>
                    <li><code>Ctrl+Z</code> — Undo</li>
                    <li><code>Ctrl+Shift+Z</code> — Redo</li>
                </ul>
            </div>
            <div class="lang-id">
                <h2>Pintasan Keyboard</h2>
                <ul>
                    <li><code>Ctrl+S</code> / <code>Cmd+S</code> — Simpan chapter saat ini</li>
                    <li><code>Ctrl+B</code> — Bold</li>
                    <li><code>Ctrl+I</code> — Italic</li>
                    <li><code>Ctrl+U</code> — Underline</li>
                    <li><code>Ctrl+Z</code> — Undo</li>
                    <li><code>Ctrl+Shift+Z</code> — Redo</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- Table of Contents -->
    <div>
        <div class="guide-toc nwp-card" style="padding:16px;">
            <div style="font-size:var(--font-size-xs); font-weight:600; color:var(--color-text-muted); margin-bottom:8px; text-transform:uppercase;">
                <span class="lang-en">On this page</span>
                <span class="lang-id">Di halaman ini</span>
            </div>
            <a href="#getting-started">
                <span class="lang-en">Getting Started</span>
                <span class="lang-id">Memulai</span>
            </a>
            <a href="#dashboard">Dashboard</a>
            <a href="#books">
                <span class="lang-en">Book Management</span>
                <span class="lang-id">Manajemen Buku</span>
            </a>
            <a href="#editor">
                <span class="lang-en">Writing Editor</span>
                <span class="lang-id">Editor Menulis</span>
            </a>
            <a href="#characters">
                <span class="lang-en">Characters</span>
                <span class="lang-id">Karakter</span>
            </a>
            <a href="#locations">
                <span class="lang-en">Locations</span>
                <span class="lang-id">Lokasi</span>
            </a>
            <a href="#plot">Plot</a>
            <a href="#world">World Building</a>
            <a href="#statistics">
                <span class="lang-en">Statistics</span>
                <span class="lang-id">Statistik</span>
            </a>
            <a href="#export">
                <span class="lang-en">Export</span>
                <span class="lang-id">Ekspor</span>
            </a>
            <a href="#darkmode">
                <span class="lang-en">Dark Mode</span>
                <span class="lang-id">Mode Gelap</span>
            </a>
            <a href="#shortcuts">
                <span class="lang-en">Shortcuts</span>
                <span class="lang-id">Pintasan</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function setLang(lang) {
    document.documentElement.setAttribute('data-lang', lang);
    localStorage.setItem('miwriter-lang', lang);
    document.getElementById('lang-btn-en').classList.toggle('active', lang === 'en');
    document.getElementById('lang-btn-id').classList.toggle('active', lang === 'id');
    // Also update sidebar toggle
    const sidebarBtn = document.getElementById('lang-toggle-sidebar');
    if (sidebarBtn) sidebarBtn.textContent = lang.toUpperCase();
}

// Init language
(function() {
    const saved = localStorage.getItem('miwriter-lang') || 'en';
    setLang(saved);
})();
</script>
@endpush
@endsection
