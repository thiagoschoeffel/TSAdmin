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
</div>
