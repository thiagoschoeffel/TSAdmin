@extends('layouts.app')

@section('title', 'Meu perfil')

@section('content')
    <section class="card" style="max-width:560px;margin:0 auto;">
        <h1 style="font-size:1.75rem;margin-bottom:1rem;">Gerenciar minha conta</h1>
        <p style="margin-bottom:1.5rem; color:#475569;">Atualize suas informações pessoais, e-mail e senha.</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->has('profile'))
            <div class="status" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                {{ $errors->first('profile') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <label>
                Nome
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                E-mail
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <hr style="border:none;border-top:1px solid #e2e8f0;">

            <div>
                <h2 style="font-size:1.1rem;margin-bottom:0.75rem;">Alterar senha</h2>
                <p style="margin-bottom:1rem;color:#64748b;font-size:0.9rem;">
                    Informe sua senha atual para definir uma nova. Deixe em branco para manter a senha existente.
                </p>
            </div>

            <label>
                Senha atual
                <input type="password" name="current_password" autocomplete="current-password">
                @error('current_password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Nova senha
                <input type="password" name="password" autocomplete="new-password">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Confirmar nova senha
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>

            <button type="submit">Salvar alterações</button>
        </form>

        <hr style="border:none;border-top:1px solid #e2e8f0;margin:2rem 0;">

        <div style="display:flex;flex-direction:column;gap:1rem;background:#fef2f2;border-radius:0.75rem;padding:1.5rem;border:1px solid #fecaca;">
            <div>
                <h2 style="margin:0;font-size:1.2rem;color:#b91c1c;">Excluir conta</h2>
                <p style="margin-top:0.5rem;color:#991b1b;font-size:0.9rem;">
                    Esta ação é permanente. Ao confirmar, sua conta será removida e você será desconectado imediatamente.
                </p>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Tem certeza que deseja remover sua conta? Esta ação não pode ser desfeita.');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:#dc2626;">Excluir minha conta</button>
            </form>
        </div>
    </section>
@endsection
