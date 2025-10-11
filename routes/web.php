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

    Route::prefix('users')->name('users.')->middleware('check_policy:viewAny,User')->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('create', [UserManagementController::class, 'create'])->middleware('check_policy:create,User')->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->middleware('check_policy:create,User')->name('store');
        Route::get('{user}/modal', [UserManagementController::class, 'modal'])->middleware('check_policy:view')->name('modal');
        Route::get('{user}/edit', [UserManagementController::class, 'edit'])->middleware('check_policy:update')->name('edit');
        Route::patch('{user}', [UserManagementController::class, 'update'])->middleware('check_policy:update')->name('update');
        Route::delete('{user}', [UserManagementController::class, 'destroy'])->middleware('check_policy:delete')->name('destroy');
    });

    Route::prefix('clients')->name('clients.')->middleware('check_policy:viewAny,Client')->group(function (): void {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('create', [ClientController::class, 'create'])->middleware('check_policy:create,Client')->name('create');
        Route::post('/', [ClientController::class, 'store'])->middleware('check_policy:create,Client')->name('store');
        Route::get('{client}/modal', [ClientController::class, 'modal'])->middleware('check_policy:view')->name('modal');
        Route::get('{client}/edit', [ClientController::class, 'edit'])->middleware('check_policy:update')->name('edit');
        Route::patch('{client}', [ClientController::class, 'update'])->middleware('check_policy:update')->name('update');
        Route::delete('{client}', [ClientController::class, 'destroy'])->middleware('check_policy:delete')->name('destroy');
    });

    Route::prefix('products')->name('products.')->middleware('check_policy:viewAny,Product')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\ProductController::class, 'create'])->middleware('check_policy:create,Product')->name('create');
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])->middleware('check_policy:create,Product')->name('store');
        Route::get('{product}/modal', [\App\Http\Controllers\ProductController::class, 'modal'])->middleware('check_policy:view')->name('modal');
        Route::get('{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->middleware('check_policy:update')->name('edit');
        Route::patch('{product}', [\App\Http\Controllers\ProductController::class, 'update'])->middleware('check_policy:update')->name('update');
        Route::delete('{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->middleware('check_policy:delete')->name('destroy');
    });

    Route::prefix('orders')->name('orders.')->middleware('check_policy:viewAny,Order')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\OrderController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\OrderController::class, 'create'])->middleware('check_policy:create,Order')->name('create');
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'store'])->middleware('check_policy:create,Order')->name('store');
        Route::get('{order}/modal', [\App\Http\Controllers\OrderController::class, 'modal'])->middleware('check_policy:view')->name('modal');
        Route::get('{order}/edit', [\App\Http\Controllers\OrderController::class, 'edit'])->middleware('check_policy:update')->name('edit');
        Route::patch('{order}', [\App\Http\Controllers\OrderController::class, 'update'])->middleware('check_policy:update')->name('update');
        Route::delete('{order}', [\App\Http\Controllers\OrderController::class, 'destroy'])->middleware('check_policy:delete')->name('destroy');
    });

    // Order items management routes
    Route::prefix('orders/{order}/items')->name('orders.items.')->middleware('check_policy:manageItems')->group(function (): void {
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'addItem'])->name('store');
        Route::patch('{item}', [\App\Http\Controllers\OrderController::class, 'updateItem'])->name('update');
        Route::delete('{item}', [\App\Http\Controllers\OrderController::class, 'removeItem'])->name('destroy');
    });

    Route::prefix('products/{product}/components')->name('products.components.')->middleware('check_policy:manageComponents')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\ProductComponentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ProductComponentController::class, 'store'])->name('store');
        Route::patch('{componentId}', [\App\Http\Controllers\ProductComponentController::class, 'update'])->name('update');
        Route::delete('{componentId}', [\App\Http\Controllers\ProductComponentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('clients/{client}/addresses')->name('clients.addresses.')->middleware('check_policy:manageAddresses')->group(function (): void {
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
