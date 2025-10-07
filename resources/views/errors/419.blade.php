@php $status = 419; $message = 'Sessão expirou'; @endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Example App') }} - {{ $status }}</title>
  @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-50">
  <main class="min-h-screen flex items-center justify-center p-6">
    <section class="error-card">
      <h1 class="error-title">Sessão expirada ({{ $status }})</h1>
      <p class="error-message">Sua sessão expirou ou o token CSRF não é mais válido. Atualize a página ou faça login novamente.</p>
      <div class="actions">
        <a class="btn-primary" href="{{ route('login') }}">Fazer login</a>
        <a class="btn-ghost" href="{{ url('/') }}">Ir para a página inicial</a>
      </div>
    </section>
  </main>
</body>
</html>
