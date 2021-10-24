<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <title>Анализатор страниц</title>
  @env('local')
  <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">
  <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
  @endenv
  @env('production')
  <link href="{{ secure_asset('css/app.css') }}" rel="stylesheet" type="text/css">
  <script type="text/javascript" src="{{ secure_asset('js/app.js') }}"></script>
  @endenv
</head>

<body class="min-vh-100 d-flex flex-column">
  <header class="flex-shrink-0">
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
      <a class="navbar-brand" href="{{ url('/') }}">Анализатор страниц</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" href="{{ url('/') }}">Главная</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " href="{{ url('/urls') }}">Сайты</a>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <main class="flex-grow-1">
    @yield('content')
  </main>
  <footer class="border-top py-3 mt-5 flex-shrink-0">
    <div class="container-lg">
      <div class="text-center">
        <a href="https://hexlet.io/pages/about" target="_blank">Hexlet</a>
      </div>
    </div>
  </footer>
</body>

</html>