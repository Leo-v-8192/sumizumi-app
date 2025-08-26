<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ChatbotController as AdminChatbotController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // ログインしているユーザーのチャットボットだけを取得
    $chatbots = Auth::user()->chatbots;

    // ビューに$chatbots変数を渡す
    return view('dashboard', ['chatbots' => $chatbots]);

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::post('/chatbots/{chatbot}', [ChatbotController::class, 'update'])
     ->middleware('auth')
     ->name('chatbots.update');

Route::get('/logs', [LogController::class, 'show'])->name('logs.show');
Route::post('/logs/download', [LogController::class, 'download'])->name('logs.download');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // ユーザー管理
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Chatbot管理
    Route::get('/chatbots', [AdminChatbotController::class, 'index'])->name('chatbots.index');
    Route::get('/chatbots/create', [AdminChatbotController::class, 'create'])->name('chatbots.create');
    Route::post('/chatbots', [AdminChatbotController::class, 'store'])->name('chatbots.store');
    Route::get('/chatbots/{chatbot}/edit', [AdminChatbotController::class, 'edit'])->name('chatbots.edit');
    Route::patch('/chatbots/{chatbot}', [AdminChatbotController::class, 'update'])->name('chatbots.update');
    Route::delete('/chatbots/{chatbot}', [AdminChatbotController::class, 'destroy'])->name('chatbots.destroy');
});