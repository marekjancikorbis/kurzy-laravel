<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') – Kurzy</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="{{ route('courses.index') }}">Správa kurzů</a>
            <nav class="tabs" aria-label="Hlavní navigace">
                <a href="{{ route('courses.index') }}" @if (request()->routeIs('courses.*')) aria-current="page" @endif>Kurzy</a>
                <a href="{{ route('participants.index') }}" @if (request()->routeIs('participants.*')) aria-current="page" @endif>Účastníci</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="page-head">
            <div>
                <h1>@yield('title')</h1>
                @hasSection('subtitle')
                    <p class="subtitle">@yield('subtitle')</p>
                @endif
            </div>
            <div>@yield('actions')</div>
        </div>

        @if (session('status'))
            <p class="notice notice-ok" role="status">{{ session('status') }}</p>
        @endif

        {{-- Formuláře s chybami přímo u polí si souhrn vypínají sekcí "inline-errors". --}}
        @if ($errors->any() && ! View::hasSection('inline-errors'))
            <div class="notice notice-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
