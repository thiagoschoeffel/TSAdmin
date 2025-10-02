@extends('layouts.app')

@section('title', 'Meu perfil')

@section('content')
    <section class="card mx-auto max-w-2xl space-y-8">
        <div class="space-y-3">
            <h1 class="text-2xl font-semibold text-slate-900">Gerenciar minha conta</h1>
            <p class="text-sm text-slate-500">Atualize suas informações pessoais, e-mail e senha.</p>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->has('profile'))
            <div class="status status-danger">{{ $errors->first('profile') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <label class="form-label">
                Nome
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="form-input">
                @error('name')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                E-mail
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="form-input">
                @error('email')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="h-px bg-slate-200"></div>

            <div class="space-y-2">
                <h2 class="text-lg font-semibold text-slate-900">Alterar senha</h2>
                <p class="text-sm text-slate-500">Informe sua senha atual para definir uma nova. Deixe em branco para manter a senha existente.</p>
            </div>

            <label class="form-label">
                Senha atual
                <input type="password" name="current_password" autocomplete="current-password" class="form-input">
                @error('current_password')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                Nova senha
                <input type="password" name="password" autocomplete="new-password" class="form-input">
                @error('password')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                Confirmar nova senha
                <input type="password" name="password_confirmation" autocomplete="new-password" class="form-input">
            </label>

            <button type="submit" class="btn-primary">Salvar alterações</button>
        </form>

        <div class="h-px bg-slate-200"></div>

        <div class="space-y-4 rounded-xl border border-rose-100 bg-rose-50 p-6">
            <div class="space-y-2">
                <h2 class="text-lg font-semibold text-rose-700">Excluir conta</h2>
                <p class="text-sm text-rose-600">Esta ação é permanente. Ao confirmar, sua conta será removida e você será desconectado imediatamente.</p>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}"
                  data-confirm
                  data-confirm-title="Excluir conta"
                  data-confirm-message="Tem certeza que deseja remover sua conta? Esta ação não pode ser desfeita."
                  data-confirm-confirm-text="Excluir"
                  data-confirm-variant="danger">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Excluir minha conta</button>
            </form>
        </div>
    </section>
@endsection
