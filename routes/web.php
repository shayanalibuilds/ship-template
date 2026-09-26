<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/releases', [ReleaseController::class, 'index'])->name('releases.index');
Route::get('/releases/create', [ReleaseController::class, 'create'])->name('releases.create');
Route::post('/releases', [ReleaseController::class, 'store'])->name('releases.store');
Route::get('/releases/{release:slug}', [ReleaseController::class, 'show'])->name('releases.show');
