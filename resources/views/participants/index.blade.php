@extends('layouts.app')
@section('title', 'Účastníci')
@section('subtitle', 'Lidé, které lze přihlásit do kurzů')
@section('actions')
    <a class="btn btn-primary" href="{{ route('participants.create') }}">Nový účastník</a>
@endsection

@section('content')
    <div class="card">
        @if ($participants->isEmpty())
            <p class="empty">Zatím tu není žádný účastník.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Jméno</th><th>E-mail</th><th>Přihlášek</th><th><span class="visually-hidden">Akce</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $participant)
                            <tr>
                                <td class="strong">{{ $participant->name }}</td>
                                <td>{{ $participant->email }}</td>
                                <td class="num">{{ $participant->enrollments_count }}</td>
                                <td class="actions">
                                    <a class="btn btn-quiet btn-sm" href="{{ route('participants.edit', $participant) }}">Upravit</a>
                                    <form method="POST" action="{{ route('participants.destroy', $participant) }}"
                                          onsubmit="return confirm('Smazat účastníka i s jeho přihláškami?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Smazat</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
