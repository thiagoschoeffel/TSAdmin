@php $status = 404; $message = 'Página não encontrada'; @endphp

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
      <h1 class="error-title">Página não encontrada ({{ $status }})</h1>
      <p class="error-message">A página que você procura pode ter sido removida, renomeada ou está temporariamente indisponível.</p>
      <div class="actions">
        <a class="btn-primary" href="{{ url('/') }}">Voltar para a página inicial</a>
      </div>
    </section>
  </main>
</body>
</html>
