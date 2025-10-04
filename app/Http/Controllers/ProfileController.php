<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View|\Inertia\Response
    {
        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Admin/Profile/Edit');
        }

        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Perfil atualizado com sucesso.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (Client::where('created_by_id', $user->id)->exists()) {
            return redirect()
                ->route('profile.edit')
                ->withErrors([
                    'profile' => 'Não é possível remover a conta enquanto houver clientes associados ao seu usuário.',
                ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()
            ->route('home')
            ->with('status', 'Conta removida com sucesso.');
    }
}
