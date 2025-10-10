<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia; // evitamos usar diretamente antes de instalar a dependência

Route::get('/', function () {
    return \Inertia\Inertia::render('Home/Index');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Password reset (forgot password)
Route::middleware('guest')->group(function (): void {
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');
});

// Email verification routes
Route::get('/email/verify', [\App\Http\Controllers\Auth\VerificationController::class, 'notice'])->name('verification.notice');
Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])->name('verification.send');
Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function (): void {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('users')->name('users.')->middleware('admin')->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('{user}/modal', [UserManagementController::class, 'modal'])->name('modal');
        Route::get('{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::patch('{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    Route::get('clients/{client}/modal', [ClientController::class, 'modal'])->name('clients.modal');
    Route::resource('clients', ClientController::class)->except(['show']);

    Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['show']);
    Route::get('products/{product}/modal', [\App\Http\Controllers\ProductController::class, 'modal'])->name('products.modal');

    Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['show']);
    Route::get('orders/{order}/modal', [\App\Http\Controllers\OrderController::class, 'modal'])->name('orders.modal');

    // Order items management routes
    Route::prefix('orders/{order}/items')->name('orders.items.')->group(function (): void {
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'addItem'])->name('store');
        Route::patch('{item}', [\App\Http\Controllers\OrderController::class, 'updateItem'])->name('update');
        Route::delete('{item}', [\App\Http\Controllers\OrderController::class, 'removeItem'])->name('destroy');
    });

    Route::prefix('products/{product}/components')->name('products.components.')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\ProductComponentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ProductComponentController::class, 'store'])->name('store');
        Route::patch('{componentId}', [\App\Http\Controllers\ProductComponentController::class, 'update'])->name('update');
        Route::delete('{componentId}', [\App\Http\Controllers\ProductComponentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('clients/{client}/addresses')->name('clients.addresses.')->group(function (): void {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::patch('{addressId}', [AddressController::class, 'update'])->name('update');
        Route::delete('{addressId}', [AddressController::class, 'destroy'])->name('destroy');
    });
});

// Fallback para 404 dentro do grupo 'web', garantindo sessão e autenticação disponíveis
Route::fallback(function (\Illuminate\Http\Request $request) {
    // Se for requisição Inertia (X-Inertia), retorna uma resposta Inertia com status 404
    // Se for uma requisição HTML (navegador), responder com Inertia (HTML inicial)
    if (str_contains($request->header('Accept', ''), 'text/html')) {
        // Incluir a URL requisitada para que o cliente Inertia possa decidir o layout
        return \Inertia\Inertia::render('Errors/404', ['url' => $request->getRequestUri()])->toResponse($request)->setStatusCode(404);
    }

    // Para outras requisições (API/XHR sem Accept HTML), retornar 404 simples
    return response()->json(['message' => 'Not Found'], 404);
});
