<?php

use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/enrollment', [EnrollmentController::class, 'store'])->middleware('throttle:5,1')->name('enrollment.store');
Route::view('/chinh-sach', 'policies')->name('policies');
Route::get('/tac-pham/{portfolioItem}/anh', [PortfolioController::class, 'show'])->name('portfolio.image');
Route::get('/admin/login', [AdminSessionController::class, 'create'])->name('login');
Route::post('/admin/login', [AdminSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/admin/logout', [AdminSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware(['auth', 'can:manage-academy'])->group(function (): void {
    Route::redirect('/', '/admin/portfolio');
    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('admin.enrollments');
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('admin.portfolio');
    Route::post('/portfolio', [PortfolioController::class, 'store'])->name('admin.portfolio.store');
    Route::delete('/portfolio/{portfolioItem}', [PortfolioController::class, 'destroy'])->name('admin.portfolio.destroy');
});
