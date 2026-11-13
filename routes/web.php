<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ContactSubmissionController;
use App\Http\Controllers\Admin\ContentGapController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginCodeController;
use App\Http\Controllers\ChronosBookingWebhookController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\VerifyChronosSignature;
use App\Support\LandingPages;
use Illuminate\Support\Facades\Route;

// Public site — registered twice: unprefixed English (default) and Dutch under /nl
$publicRoutes = function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/docs', [HomeController::class, 'docs'])->name('docs');
    Route::get('/work', [HomeController::class, 'work'])->name('work.index');
    Route::get('/work/tag/{slug}', [HomeController::class, 'workTag'])->name('work.tag');
    Route::get('/work/{project:slug}', [HomeController::class, 'project'])->name('project.show');
    Route::get('/work/{project:slug}/preview', [HomeController::class, 'projectPreview'])
        ->name('project.preview')
        ->middleware('signed');
    Route::get('/cv.pdf', [HomeController::class, 'cv'])->name('cv');
    // Registered per locale so SetLocale can see the prefix. Posting a Dutch
    // form to the unprefixed /contact would run the request in English and
    // return an English confirmation and acknowledgement (CMS-116).
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:contact');
};

Route::group([], $publicRoutes);
Route::prefix('nl')->name('nl.')->group($publicRoutes);

// The landing pages carry a different slug per language, so they cannot share
// the $publicRoutes closure. The name pair (landing.enschede / nl.landing.enschede)
// keeps localized_route() and alternate_locale_url() working unchanged.
Route::get('/laravel-developer-enschede', [LandingController::class, 'enschede'])->name('landing.enschede');
Route::get('/nl/laravel-ontwikkelaar-enschede', [LandingController::class, 'enschede'])->name('nl.landing.enschede');
Route::get('/laravel-developer-remote', [LandingController::class, 'remote'])->name('landing.remote');
Route::get('/nl/laravel-ontwikkelaar-remote', [LandingController::class, 'remote'])->name('nl.landing.remote');

foreach (LandingPages::all() as $key => $page) {
    Route::get('/'.$page['slug']['en'], [LandingController::class, 'page'])->name("landing.{$key}");
    Route::get('/nl/'.$page['slug']['nl'], [LandingController::class, 'page'])->name("nl.landing.{$key}");
}

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /admin\n\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']))->name('robots');
Route::get('/og/home.png', [OgImageController::class, 'home'])->name('og.home');
Route::get('/og/work/{project:slug}.png', [OgImageController::class, 'project'])->name('og.project');

// Chronos pushes each booking and cancellation here (CMS-153). A server calls
// it, not a browser, so the whole web group is dropped: no session, cookies or
// CSRF token. The signature middleware is the only thing guarding it.
Route::post('/webhooks/chronos/bookings', ChronosBookingWebhookController::class)
    ->name('webhooks.chronos.bookings')
    ->withoutMiddleware('web')
    ->middleware(['throttle:chronos-webhook', VerifyChronosSignature::class]);

// Admin auth
Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.login.code.')->group(function () {
    Route::post('/login/code', [LoginCodeController::class, 'send'])->name('send')->middleware('throttle:login');
    Route::get('/login/code', [LoginCodeController::class, 'show'])->name('challenge');
    Route::post('/login/code/verify', [LoginCodeController::class, 'verify'])->name('verify')->middleware('throttle:login');
});

// Admin panel (CMS) — the only Inertia surface, so the Inertia middleware is
// scoped here rather than to the whole web group.
Route::middleware(['auth', HandleInertiaRequests::class])->prefix('admin')->name('admin.')->group(function () {
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

    Route::resource('users', UserController::class)->except(['show']);

    Route::get('/contact-submissions', [ContactSubmissionController::class, 'index'])->name('contact-submissions.index');
    Route::post('/contact-submissions/{contactSubmission}/read', [ContactSubmissionController::class, 'markRead'])->name('contact-submissions.read');
    Route::post('/contact-submissions/{contactSubmission}/unread', [ContactSubmissionController::class, 'markUnread'])->name('contact-submissions.unread');
    Route::post('/contact-submissions/{contactSubmission}/replied', [ContactSubmissionController::class, 'markReplied'])->name('contact-submissions.replied');
    Route::post('/contact-submissions/{contactSubmission}/unreplied', [ContactSubmissionController::class, 'markUnreplied'])->name('contact-submissions.unreplied');
    Route::post('/contact-submissions/{contactSubmission}/note', [ContactSubmissionController::class, 'saveNote'])->name('contact-submissions.note');
    Route::delete('/contact-submissions/{contactSubmission}', [ContactSubmissionController::class, 'destroy'])->name('contact-submissions.destroy');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/read', [BookingController::class, 'markRead'])->name('bookings.read');
    Route::post('/bookings/{booking}/unread', [BookingController::class, 'markUnread'])->name('bookings.unread');

    Route::get('/content-gaps', [ContentGapController::class, 'index'])->name('content-gaps.index');

    Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
});

Route::fallback(fn () => abort(404));
