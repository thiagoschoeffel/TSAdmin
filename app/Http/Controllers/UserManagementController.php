<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Rules\UserHasNoClients;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuário criado com sucesso.');
    }

    public function edit(User $user): View
    {
        $this->ensureNotCurrentUser($user);

        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureNotCurrentUser($user);

        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureNotCurrentUser($user);

        validator(
            ['user_id' => $user->id],
            ['user_id' => [new UserHasNoClients()]]
        )->validate();

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuário removido com sucesso.');
    }

    protected function ensureNotCurrentUser(User $user): void
    {
        if (auth()->id() === $user->id) {
            abort(403, 'Você não pode realizar esta ação no próprio usuário.');
        }
    }
}
