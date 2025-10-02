@php
    $user = $user ?? null;
    $requirePassword = $requirePassword ?? false;
@endphp

<div style="display:grid;gap:1.5rem;">
    <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <label>
            Nome
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required autocomplete="name">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </label>

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required autocomplete="email">
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <fieldset style="border:none;padding:0;display:grid;gap:0.75rem;">
        <legend style="font-weight:700;color:#1e293b;margin-bottom:0.5rem;">Credenciais de acesso</legend>
        <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            <label>
                {{ $requirePassword ? 'Senha' : 'Nova senha' }}
                <input type="password" name="password" autocomplete="new-password" {{ $requirePassword ? 'required' : '' }}>
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Confirmar senha
                <input type="password" name="password_confirmation" {{ $requirePassword ? 'required' : '' }} autocomplete="new-password">
            </label>
        </div>
        @unless($requirePassword)
            <p style="margin:0;color:#64748b;font-size:0.9rem;">Preencha apenas se desejar definir uma nova senha para o usuário.</p>
        @endunless
    </fieldset>
</div>
