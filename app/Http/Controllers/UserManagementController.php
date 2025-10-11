<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Rules\UserHasNoClients;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

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

        $users = $query
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified_at' => $user->email_verified_at,
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->get('status'),
            ],
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'resources' => config('permissions.resources', []),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        $data['permissions'] = $this->preparePermissions(
            $data['role'],
            $data['permissions'] ?? [],
            $request->input('modules', [])
        );

        // Usuários criados pelo admin não precisam verificar e-mail
        $data['email_verified_at'] = now();

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuário criado com sucesso.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);
        $this->ensureNotCurrentUser($user);

        return Inertia::render('Admin/Users/Edit', [
            'resources' => config('permissions.resources', []),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'role' => $user->role,
                'permissions' => $user->permissions ?? [],
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $this->ensureNotCurrentUser($user);

        $payload = $request->safe()->only(['name', 'email', 'status', 'role']);

        // Reaplicar permissões de acordo com a role e entrada enviada
        $payload['permissions'] = $this->preparePermissions(
            $payload['role'],
            $request->input('permissions', []),
            $request->input('modules', [])
        );

        $user->fill($payload);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        // Garantir que usuários criados/editados pelo admin tenham e-mail verificado
        if (!$user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
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
        $this->authorize('view', $user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'permissions' => $user->permissions ?? [],
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at?->format('d/m/Y H:i'),
                'updated_at' => $user->updated_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    protected function ensureNotCurrentUser(User $user): void
    {
        if (Auth::check() && Auth::id() === $user->id) {
            abort(403, 'Você não pode realizar esta ação no próprio usuário.');
        }
    }

    protected function preparePermissions(string $role, array $input, array $modules = []): array
    {
        // Admin sempre possui todas as permissões
        if ($role === 'admin') {
            return $this->allPermissionsMatrix();
        }

        $allowed = [];
        $resources = config('permissions.resources', []);

        foreach ($resources as $key => $resource) {
            $abilities = array_keys($resource['abilities'] ?? []);
            $moduleEnabled = (bool)($modules[$key] ?? false);
            foreach ($abilities as $ability) {
                $allowed[$key][$ability] = $moduleEnabled ? (bool)($input[$key][$ability] ?? false) : false;
            }
        }

        return $allowed;
    }

    protected function allPermissionsMatrix(): array
    {
        $matrix = [];
        $resources = config('permissions.resources', []);
        foreach ($resources as $key => $resource) {
            foreach (array_keys($resource['abilities'] ?? []) as $ability) {
                $matrix[$key][$ability] = true;
            }
        }
        return $matrix;
    }
}
