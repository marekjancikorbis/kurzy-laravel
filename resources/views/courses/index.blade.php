@extends('layouts.app')
@section('title', 'Kurzy')
@section('subtitle', 'Nabídka kurzů a jejich obsazenost')
@section('actions')
    <a class="btn btn-primary" href="{{ route('courses.create') }}">Nový kurz</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('courses.index') }}" class="card card-pad toolbar">
        <div class="field">
            <label for="from">Termín od</label>
            <input type="date" id="from" name="from" value="{{ $filter['from'] ?? '' }}">
        </div>
        <div class="field">
            <label for="to">Termín do</label>
            <input type="date" id="to" name="to" value="{{ $filter['to'] ?? '' }}">
        </div>
        <label class="check grow">
            <input type="checkbox" name="free" value="1" @checked($filter['free'] ?? false)>
            Jen s volnými místy
        </label>
        <button type="submit" class="btn">Filtrovat</button>
        @if (array_filter($filter))
            <a class="btn btn-quiet" href="{{ route('courses.index') }}">Zrušit filtr</a>
        @endif
    </form>

    <div class="card">
        @if ($courses->isEmpty())
            <p class="empty">{{ array_filter($filter) ? 'Filtru neodpovídá žádný kurz.' : 'Zatím tu není žádný kurz.' }}</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kód</th><th>Název</th><th>Termín</th><th>Obsazenost</th><th>Volná místa</th>
                            <th><span class="visually-hidden">Akce</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                            @php($free = max(0, $course->capacity - $course->enrolled_count))
                            <tr>
                                <td class="code">{{ $course->code }}</td>
                                <td><a class="strong" href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></td>
                                <td class="num">{{ $course->starts_at->format('j. n. Y H:i') }}</td>
                                <td>
                                    <div class="meter">
                                        <div @class(['meter-bar', 'is-full' => ! $free]) aria-hidden="true">
                                            <span style="width: {{ min(100, round($course->enrolled_count / $course->capacity * 100)) }}%"></span>
                                        </div>
                                        <span class="num">{{ $course->enrolled_count }} / {{ $course->capacity }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($free)
                                        <span class="pill pill-ok">Volno {{ $free }}</span>
                                    @else
                                        <span class="pill pill-danger">Obsazeno</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <a class="btn btn-quiet btn-sm" href="{{ route('courses.edit', $course) }}">Upravit</a>
                                    <form method="POST" action="{{ route('courses.destroy', $course) }}"
                                          onsubmit="return confirm('Smazat kurz i s jeho přihláškami?')">
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
