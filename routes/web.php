<?php

use App\Http\Controllers\Admin\ContactSubmissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginCodeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Public site — registered twice: unprefixed English (default) and Dutch under /nl
$publicRoutes = function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/docs', [HomeController::class, 'docs'])->name('docs');
    Route::get('/work', [HomeController::class, 'work'])->name('work.index');
    Route::get('/work/tag/{tag}', [HomeController::class, 'workTag'])->name('work.tag');
    Route::get('/work/{project:slug}', [HomeController::class, 'project'])->name('project.show');
    Route::get('/blog', [HomeController::class, 'blog'])->name('blog.index');
    Route::get('/blog/{post:slug}', [HomeController::class, 'post'])->name('blog.show');
};

Route::group([], $publicRoutes);
Route::prefix('nl')->name('nl.')->group($publicRoutes);

Route::get('/cv.pdf', [HomeController::class, 'cv'])->name('cv');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /admin\n\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']))->name('robots');
Route::get('/og/home.png', [OgImageController::class, 'home'])->name('og.home');
Route::get('/og/work/{project:slug}.png', [OgImageController::class, 'project'])->name('og.project');
Route::get('/og/blog/{post:slug}.png', [OgImageController::class, 'post'])->name('og.post');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:contact');

// Admin auth
Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.login.code.')->group(function () {
    Route::post('/login/code', [LoginCodeController::class, 'send'])->name('send')->middleware('throttle:login');
    Route::get('/login/code', [LoginCodeController::class, 'show'])->name('challenge');
    Route::post('/login/code/verify', [LoginCodeController::class, 'verify'])->name('verify')->middleware('throttle:login');
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

    Route::get('/posts/trash', [PostController::class, 'trash'])->name('posts.trash');
    Route::post('/posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
    Route::delete('/posts/{id}/force', [PostController::class, 'forceDelete'])->name('posts.forceDelete');
    Route::resource('posts', PostController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', UserController::class)->except(['show']);

    Route::get('/contact-submissions', [ContactSubmissionController::class, 'index'])->name('contact-submissions.index');
    Route::post('/contact-submissions/{contactSubmission}/read', [ContactSubmissionController::class, 'markRead'])->name('contact-submissions.read');
    Route::post('/contact-submissions/{contactSubmission}/unread', [ContactSubmissionController::class, 'markUnread'])->name('contact-submissions.unread');
    Route::delete('/contact-submissions/{contactSubmission}', [ContactSubmissionController::class, 'destroy'])->name('contact-submissions.destroy');

    Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
});

Route::fallback(fn () => abort(404));
