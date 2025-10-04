@php
    $user = $user ?? null;
    $requirePassword = $requirePassword ?? false;
    $statusIsActive = old('status', $user->status ?? 'active') === 'active';
    $role = old('role', $user->role ?? 'user');
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="form-label">
            Nome
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required autocomplete="name" class="form-input">
            @error('name')
                <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="form-label">
            E-mail
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required autocomplete="email" class="form-input">
            @error('email')
                <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
            @enderror
        </label>

        <div class="switch-field sm:col-span-2" data-status-control>
            <span class="switch-label">Status do usuário</span>
            <label class="relative inline-flex h-7 w-12 cursor-pointer items-center">
                <input type="hidden" name="status" value="inactive">
                <input type="checkbox" name="status" value="active" data-status-toggle class="peer sr-only" {{ $statusIsActive ? 'checked' : '' }}>
                <span class="pointer-events-none block h-full w-full rounded-full bg-slate-300 transition peer-checked:bg-blue-600 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-blue-500/60"></span>
                <span class="pointer-events-none absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
            </label>
            <span
                class="switch-status {{ $statusIsActive ? '' : 'inactive' }}"
                data-status-label
                data-status-active="Ativo"
                data-status-inactive="Inativo"
            >
                {{ $statusIsActive ? 'Ativo' : 'Inativo' }}
            </span>
        </div>
        @error('status')
            <span class="text-sm font-medium text-rose-600 sm:col-span-2">{{ $message }}</span>
        @enderror
    </div>

    <fieldset class="space-y-3">
        <legend class="text-sm font-semibold text-slate-700">Credenciais de acesso</legend>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="form-label">
                {{ $requirePassword ? 'Senha' : 'Nova senha' }}
                <input type="password" name="password" autocomplete="new-password" {{ $requirePassword ? 'required' : '' }} class="form-input">
                @error('password')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                Confirmar senha
                <input type="password" name="password_confirmation" {{ $requirePassword ? 'required' : '' }} autocomplete="new-password" class="form-input">
            </label>
        </div>
        @unless($requirePassword)
            <p class="text-sm text-slate-500">Preencha apenas se desejar definir uma nova senha para o usuário.</p>
        @endunless
    </fieldset>
    <div class="space-y-2">
        <span class="text-sm font-semibold text-slate-700">Perfil de acesso</span>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="form-label">
                Função
                <select name="role" class="form-select" required data-role-select>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Usuário comum</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </label>
            <p class="text-sm text-slate-500">
                Administradores podem gerenciar usuários. Demais perfis possuem acesso restrito às próprias operações.
            </p>
        </div>
        @error('role')
            <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="space-y-2" data-permissions-container>
        <span class="text-sm font-semibold text-slate-700">Permissões</span>
        @php
            $resources = config('permissions.resources');
            $isAdmin = $role === 'admin';
            $currentPermissions = old('permissions', $user->permissions ?? []);
        @endphp
        <div class="space-y-4">
            @foreach ($resources as $key => $resource)
                @php
                    $hasAnyAbility = false;
                    foreach (($resource['abilities'] ?? []) as $abKey => $_) {
                        if (!empty($currentPermissions[$key][$abKey])) { $hasAnyAbility = true; break; }
                    }
                    $moduleEnabled = $isAdmin ? true : old("modules.$key", $hasAnyAbility ? '1' : '0') === '1';
                @endphp
                <fieldset class="rounded-xl border border-slate-200 bg-slate-50 p-4" data-permissions-resource="{{ $key }}" {{ $isAdmin ? 'disabled' : '' }}>
                    <legend class="flex items-center justify-between gap-4 px-1 text-sm font-semibold text-slate-800">
                        <span>{{ $resource['label'] }}</span>
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-600">
                            <input type="hidden" name="modules[{{ $key }}]" value="0">
                            <input type="checkbox" name="modules[{{ $key }}]" value="1"
                                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"
                                   data-module-toggle data-module="{{ $key }}"
                                   {{ $moduleEnabled ? 'checked' : '' }}
                                   {{ $isAdmin ? 'disabled' : '' }}>
                            <span>Acesso ao módulo</span>
                        </label>
                    </legend>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" data-abilities>
                        @foreach ($resource['abilities'] as $ability => $label)
                            @php
                                $checked = $isAdmin ? true : ($moduleEnabled && (bool)($currentPermissions[$key][$ability] ?? false));
                            @endphp
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="checkbox"
                                       name="permissions[{{ $key }}][{{ $ability }}]"
                                       value="1"
                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"
                                       {{ $checked ? 'checked' : '' }}
                                       {{ $isAdmin ? 'disabled' : ($moduleEnabled ? '' : 'disabled') }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-500 {{ $isAdmin ? '' : 'hidden' }}" data-admin-permissions-note>
                        Todas as permissões e módulos estão habilitados para administradores.
                    </p>
                </fieldset>
            @endforeach
        </div>
    </div>
</div>
