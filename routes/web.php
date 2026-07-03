<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [HomeController::class, 'index'])->name('home');

// Admin auth
Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.attempt');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.two-factor.')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])->name('challenge.verify');
});

// Admin panel (CMS)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::resource('testimonials', TestimonialController::class)->except(['show']);
    Route::resource('skills', SkillController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});
