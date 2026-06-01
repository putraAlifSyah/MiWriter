<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\WorldElementController;
use App\Http\Controllers\WritingTargetController;
use Illuminate\Support\Facades\Route;

// Public routes (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Books
    Route::resource('books', BookController::class)->except(['edit']);
    Route::post('books/{book}/cover', [BookController::class, 'uploadCover'])->name('books.cover.upload');
    Route::delete('books/{book}/cover', [BookController::class, 'removeCover'])->name('books.cover.remove');

    // Chapters (nested under books)
    Route::prefix('books/{book}')->middleware('book.owner')->group(function () {
        Route::post('chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::put('chapters/reorder', [ChapterController::class, 'reorder'])->name('chapters.reorder');
        Route::get('chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
        Route::put('chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::put('chapters/{chapter}/content', [ChapterController::class, 'saveContent'])->name('chapters.content');
        Route::delete('chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
    });

    // Characters
    Route::prefix('books/{book}')->middleware('book.owner')->group(function () {
        Route::get('characters', [CharacterController::class, 'index'])->name('characters.index');
        Route::post('characters', [CharacterController::class, 'store'])->name('characters.store');
        Route::get('characters/relationships', [CharacterController::class, 'relationships'])->name('characters.relationships');
        Route::put('characters/{character}', [CharacterController::class, 'update'])->name('characters.update');
        Route::delete('characters/{character}', [CharacterController::class, 'destroy'])->name('characters.destroy');
        Route::post('characters/{character}/image', [CharacterController::class, 'uploadImage'])->name('characters.image');
    });

    // Locations
    Route::prefix('books/{book}')->middleware('book.owner')->group(function () {
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
        Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
        Route::get('locations/hierarchy', [LocationController::class, 'hierarchy'])->name('locations.hierarchy');
        Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
        Route::post('locations/{location}/image', [LocationController::class, 'uploadImage'])->name('locations.image');
    });

    // Plot
    Route::prefix('books/{book}')->middleware('book.owner')->group(function () {
        Route::get('plot', [PlotController::class, 'index'])->name('plot.index');
        Route::post('plot', [PlotController::class, 'store'])->name('plot.store');
        Route::put('plot/reorder', [PlotController::class, 'reorder'])->name('plot.reorder');
        Route::put('plot/{plotPoint}', [PlotController::class, 'update'])->name('plot.update');
        Route::delete('plot/{plotPoint}', [PlotController::class, 'destroy'])->name('plot.destroy');
    });

    // World Building
    Route::prefix('books/{book}')->middleware('book.owner')->group(function () {
        Route::get('world', [WorldElementController::class, 'index'])->name('world.index');
        Route::post('world', [WorldElementController::class, 'store'])->name('world.store');
        Route::put('world/{element}', [WorldElementController::class, 'update'])->name('world.update');
        Route::delete('world/{element}', [WorldElementController::class, 'destroy'])->name('world.destroy');
    });

    // Writing Targets
    Route::prefix('books/{book}')->middleware('book.owner')->group(function () {
        Route::post('targets', [WritingTargetController::class, 'store'])->name('targets.store');
        Route::put('targets/{target}', [WritingTargetController::class, 'update'])->name('targets.update');
    });

    // Statistics
    Route::get('books/{book}/statistics', [StatisticsController::class, 'show'])->name('statistics.show')->middleware('book.owner');
    Route::get('statistics/heatmap', [StatisticsController::class, 'heatmap'])->name('statistics.heatmap');
    Route::get('books/{book}/statistics/progress', [StatisticsController::class, 'progressChart'])->name('statistics.progress')->middleware('book.owner');

    // Export
    Route::get('books/{book}/export', [ExportController::class, 'book'])->name('export.book')->middleware('book.owner');
    Route::get('chapters/{chapter}/export', [ExportController::class, 'chapter'])->name('export.chapter');

    // Search
    Route::get('books/{book}/search', [SearchController::class, 'search'])->name('search')->middleware('book.owner');

    // Settings
    Route::get('settings', [SettingsController::class, 'show'])->name('settings');
    Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('settings/avatar', [SettingsController::class, 'uploadAvatar'])->name('settings.avatar');
    Route::delete('settings/account', [SettingsController::class, 'deleteAccount'])->name('settings.account');
    Route::put('settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
});
