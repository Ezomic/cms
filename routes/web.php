<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [HomeController::class, 'index'])->name('home');

// Admin auth
Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.attempt');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Admin panel (CMS)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/projects/trash', [ProjectController::class, 'trash'])->name('projects.trash');
    Route::post('/projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('/projects/{id}/force', [ProjectController::class, 'forceDelete'])->name('projects.forceDelete');
    Route::resource('projects', ProjectController::class)->except(['show']);

    Route::get('/testimonials/trash', [TestimonialController::class, 'trash'])->name('testimonials.trash');
    Route::post('/testimonials/{id}/restore', [TestimonialController::class, 'restore'])->name('testimonials.restore');
    Route::delete('/testimonials/{id}/force', [TestimonialController::class, 'forceDelete'])->name('testimonials.forceDelete');
    Route::resource('testimonials', TestimonialController::class)->except(['show']);

    Route::get('/skills/trash', [SkillController::class, 'trash'])->name('skills.trash');
    Route::post('/skills/{id}/restore', [SkillController::class, 'restore'])->name('skills.restore');
    Route::delete('/skills/{id}/force', [SkillController::class, 'forceDelete'])->name('skills.forceDelete');
    Route::resource('skills', SkillController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
