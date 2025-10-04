<div class="space-y-6">
    <header class="space-y-2">
        <h2 id="user-details-modal-title" class="text-xl font-semibold text-slate-900">{{ $user->name }}</h2>
    </header>

    <section class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900">Informações gerais</h3>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-sm font-semibold text-slate-500">Status</dt>
                <dd>
                    <span class="{{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->status === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-semibold text-slate-500">E-mail</dt>
                <dd class="text-sm text-slate-800">{{ $user->email }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-semibold text-slate-500">Perfil</dt>
                <dd class="text-sm text-slate-800">
                    {{ $user->role === 'admin' ? 'Administrador' : 'Usuário comum' }}
                </dd>
            </div>
        </dl>
    </section>

    <section class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900">Auditoria</h3>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-sm font-semibold text-slate-500">Criado em</dt>
                <dd class="text-sm text-slate-800">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-semibold text-slate-500">Atualizado em</dt>
                <dd class="text-sm text-slate-800">{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900">Permissões</h3>
        @php $resources = config('permissions.resources', []); @endphp

        @if ($user->isAdmin())
            <p class="text-sm text-slate-600">Administrador: todas as permissões habilitadas.</p>
        @else
            @php $perms = $user->permissions ?? []; @endphp
            <dl class="space-y-3">
                @foreach ($resources as $key => $resource)
                    @php
                        $abilities = $resource['abilities'] ?? [];
                        $granted = [];
                        foreach ($abilities as $ability => $label) {
                            if (data_get($perms, $key.'.'.$ability, false)) {
                                $granted[] = $label;
                            }
                        }
                    @endphp
                    <div class="space-y-1">
                        <dt class="text-sm font-semibold text-slate-500">{{ $resource['label'] }}</dt>
                        <dd class="text-sm text-slate-800">
                            @if (empty($granted))
                                <span class="text-slate-400">Nenhuma permissão</span>
                            @else
                                {{ implode(', ', $granted) }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </section>
</div>
