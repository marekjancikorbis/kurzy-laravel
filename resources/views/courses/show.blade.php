@extends('layouts.app')
@section('title', $course->name)
@section('subtitle', 'Kód '.$course->code)
@section('actions')
    <a class="btn" href="{{ route('courses.edit', $course) }}">Upravit kurz</a>
@endsection

@section('content')
    @php($free = max(0, $course->capacity - $enrolledCount))

    <div class="card card-pad">
        <dl class="stats">
            <div><dt>Termín</dt><dd>{{ $course->starts_at->format('j. n. Y H:i') }}</dd></div>
            <div><dt>Obsazenost</dt><dd>{{ $enrolledCount }} / {{ $course->capacity }}</dd></div>
            <div>
                <dt>Volná místa</dt>
                <dd>@if ($free) {{ $free }} @else <span class="pill pill-danger">Obsazeno</span> @endif</dd>
            </div>
        </dl>
    </div>

    <div class="card card-pad">
        <h2>Přihlásit účastníka</h2>
        @if ($participants->isEmpty())
            <p class="hint">Nejdřív <a href="{{ route('participants.create') }}">přidejte účastníka</a>.</p>
        @else
            <form method="POST" action="{{ route('enrollments.store', $course) }}" class="toolbar">
                @csrf
                <div class="field grow">
                    <label for="participant_id">Účastník</label>
                    <select id="participant_id" name="participant_id" required>
                        <option value="">Vyberte účastníka</option>
                        @foreach ($participants as $participant)
                            <option value="{{ $participant->id }}" @selected(old('participant_id') == $participant->id)>
                                {{ $participant->name }} ({{ $participant->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Přihlásit</button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-pad" style="padding-bottom: 0"><h2>Přihlášky</h2></div>
        @if ($course->enrollments->isEmpty())
            <p class="empty">Zatím není nikdo přihlášen.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Účastník</th><th>E-mail</th><th>Přihlášen</th><th>Stav</th><th>Změnit stav</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($course->enrollments as $enrollment)
                            <tr>
                                <td class="strong">{{ $enrollment->participant->name }}</td>
                                <td>{{ $enrollment->participant->email }}</td>
                                <td class="num">{{ $enrollment->enrolled_at->format('j. n. Y H:i') }}</td>
                                <td>
                                    <span @class(['pill', 'pill-ok' => $enrollment->status === 'enrolled', 'pill-danger' => $enrollment->status === 'cancelled'])>
                                        {{ \App\Models\Enrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('enrollments.update', $enrollment) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <select name="status" aria-label="Stav přihlášky – {{ $enrollment->participant->name }}">
                                            @foreach (\App\Models\Enrollment::STATUSES as $value => $label)
                                                <option value="{{ $value }}" @selected($enrollment->status === $value)>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm">Uložit</button>
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
