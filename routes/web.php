<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ClientController;
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
    Route::resource('clients', ClientController::class);

    // Rota de exemplo para Inertia (só registra se o pacote estiver instalado)
    if (class_exists(\Inertia\Inertia::class)) {
        Route::get('/inertia-demo', function () {
            return \Inertia\Inertia::render('Demo/Hello', [
                'title' => 'Inertia Demo',
            ]);
        })->name('inertia.demo');
    }
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
