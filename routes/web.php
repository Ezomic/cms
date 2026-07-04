<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OgImageController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/docs', [HomeController::class, 'docs'])->name('docs');
Route::get('/og/home.png', [OgImageController::class, 'home'])->name('og.home');
Route::get('/og/work/{project:slug}.png', [OgImageController::class, 'project'])->name('og.project');
Route::get('/work', [HomeController::class, 'work'])->name('work.index');
Route::get('/work/{project:slug}', [HomeController::class, 'project'])->name('project.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:contact');

// Admin auth
Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.attempt')->middleware('throttle:login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.two-factor.')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])->name('challenge.verify')->middleware('throttle:login');
});

// Admin panel (CMS)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
    Route::get('/projects/trash', [ProjectController::class, 'trash'])->name('projects.trash');
    Route::post('/projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('/projects/{id}/force', [ProjectController::class, 'forceDelete'])->name('projects.forceDelete');
    Route::resource('projects', ProjectController::class)->except(['show']);

    Route::get('/testimonials/trash', [TestimonialController::class, 'trash'])->name('testimonials.trash');
    Route::post('/testimonials/{id}/restore', [TestimonialController::class, 'restore'])->name('testimonials.restore');
    Route::delete('/testimonials/{id}/force', [TestimonialController::class, 'forceDelete'])->name('testimonials.forceDelete');
    Route::resource('testimonials', TestimonialController::class)->except(['show']);

    Route::post('/skills/reorder', [SkillController::class, 'reorder'])->name('skills.reorder');
    Route::get('/skills/trash', [SkillController::class, 'trash'])->name('skills.trash');
    Route::post('/skills/{id}/restore', [SkillController::class, 'restore'])->name('skills.restore');
    Route::delete('/skills/{id}/force', [SkillController::class, 'forceDelete'])->name('skills.forceDelete');
    Route::resource('skills', SkillController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::resource('users', UserController::class)->except(['show']);

    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});
