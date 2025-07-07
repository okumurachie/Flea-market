<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [UserController::class, 'index']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage/profile', [UserController::class, 'profile']);
    Route::post('/profile', [UserController::class, 'store'])->name('profile/store');
    Route::put('/profile/{profile}', [UserController::class, 'update'])->name('profile.update');
});
