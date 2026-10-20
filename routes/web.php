<?php

use App\Http\Controllers\AdminPortfolioCategoryController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\WorkshopAdminController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/enrollment', [EnrollmentController::class, 'store'])->middleware('throttle:consultation-form')->name('enrollment.store');
Route::view('/chinh-sach', 'policies')->name('policies');
Route::get('/tac-pham/{portfolioItem}/anh', [PortfolioController::class, 'show'])->name('portfolio.image');
Route::redirect('/workshop/the-art-of-elevation-2026', '/workshop/the-back', 302);
Route::redirect('/workshop-admin/the-art-of-elevation-2026', '/workshop-admin/the-back', 302);
Route::get('/workshop/{workshop:slug}', [WorkshopController::class, 'show'])->name('workshop.show');
Route::post('/workshop/{workshop:slug}/register', [WorkshopController::class, 'store'])->middleware('throttle:workshop-registration')->name('workshop.register');
Route::prefix('workshop-admin/{workshop:slug}')->middleware(['auth', 'can:manage-academy'])->group(function (): void {
    Route::get('/', [WorkshopAdminController::class, 'index'])->name('workshop.admin');
    Route::patch('/', [WorkshopAdminController::class, 'updateEvent'])->name('workshop.update');
    Route::patch('/capacity', [WorkshopAdminController::class, 'updateCapacity'])->name('workshop.capacity.update');
    Route::get('/guests.csv', [WorkshopAdminController::class, 'export'])->name('workshop.export');
    Route::patch('/registrations/{registration}', [WorkshopAdminController::class, 'updateRegistration'])->name('workshop.registration.update');
});
Route::get('/admin/login', [AdminSessionController::class, 'create'])->name('login');
Route::post('/admin/login', [AdminSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/admin/logout', [AdminSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware(['auth', 'can:manage-academy'])->group(function (): void {
    Route::redirect('/', '/admin/portfolio');
    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('admin.enrollments');
    Route::patch('/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('admin.enrollments.update');
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('admin.portfolio');
    Route::post('/portfolio', [PortfolioController::class, 'store'])->name('admin.portfolio.store');
    Route::post('/portfolio/bulk', [PortfolioController::class, 'bulk'])->name('admin.portfolio.bulk');
    Route::get('/portfolio/{portfolioItem}/image', [PortfolioController::class, 'preview'])->name('admin.portfolio.image');
    Route::get('/portfolio/{portfolioItem}/edit', [PortfolioController::class, 'edit'])->name('admin.portfolio.edit');
    Route::put('/portfolio/{portfolioItem}', [PortfolioController::class, 'update'])->name('admin.portfolio.update');
    Route::delete('/portfolio/{portfolioItem}', [PortfolioController::class, 'destroy'])->name('admin.portfolio.destroy');
    Route::get('/portfolio-categories', [AdminPortfolioCategoryController::class, 'index'])->name('admin.portfolio.categories');
    Route::post('/portfolio-categories', [AdminPortfolioCategoryController::class, 'store'])->name('admin.portfolio.categories.store');
    Route::get('/portfolio-categories/{category}/edit', [AdminPortfolioCategoryController::class, 'edit'])->name('admin.portfolio.categories.edit');
    Route::put('/portfolio-categories/{category}', [AdminPortfolioCategoryController::class, 'update'])->name('admin.portfolio.categories.update');
    Route::delete('/portfolio-categories/{category}', [AdminPortfolioCategoryController::class, 'destroy'])->name('admin.portfolio.categories.destroy');
});
