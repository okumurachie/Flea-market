<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;




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

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    $user = $request->user();
    $profile = $user->profile;

    if (!$profile || !$profile->profile_completed) {
        return redirect('/mypage/profile');
    }

    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('/', [UserController::class, 'index'])->name('home');
Route::get('/item/{id}', [ItemController::class, 'show'])->name('detail');
Route::get('/purchase/success', [PurchaseController::class, 'success'])->name('purchase.success');
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle']);
Route::get('/purchase/{id}/cancel', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage', [UserController::class, 'mypage'])->name('mypage');
    Route::get('/mypage/profile', [UserController::class, 'profile']);
    Route::post('/mypage/profile', [UserController::class, 'store'])->name('profile.store');
    Route::put('/mypage/profile/{profile}', [UserController::class, 'update'])->name('profile.update');
    Route::post('/item/favorite/toggle', [ItemController::class, 'toggleFavorite']);
    Route::post('/comments', [ItemController::class, 'addComment'])->name('comments.add');
    Route::get('/sell', [ItemController::class, 'create']);
    Route::post('/sell', [ItemController::class, 'store']);
    Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::get('/purchase/address/{item_id}', [UserController::class, 'showEditAddressForm']);
    Route::patch('/purchase/{id}', [UserController::class, 'editAddress'])->name('address.update');
    Route::post('/purchase/checkout', [PurchaseController::class, 'checkout'])->name('purchase.checkout');
    Route::get('/konbini/confirm', [PurchaseController::class, 'showKonbiniConfirm'])->name('konbini.confirm');
    Route::get('/chat/{purchase}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{purchase}/message', [ChatController::class, 'store'])->name('chat.store');
});
