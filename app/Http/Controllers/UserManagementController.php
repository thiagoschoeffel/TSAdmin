<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Rules\UserHasNoClients;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($inner) use ($search): void {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('users.index', [
            'users' => $query->orderBy('name')->paginate(10)->withQueryString(),
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

        $user->fill($request->safe()->only(['name', 'email', 'status', 'role']));

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

    public function modal(User $user): JsonResponse
    {
        return response()->json([
            'html' => view('users.partials.details-modal', compact('user'))->render(),
        ]);
    }

    protected function ensureNotCurrentUser(User $user): void
    {
        if (auth()->id() === $user->id) {
            abort(403, 'Você não pode realizar esta ação no próprio usuário.');
        }
    }
}
