@extends('layouts.app')
@section('title', $course->exists ? 'Upravit kurz' : 'Nový kurz')
@section('inline-errors', '1')

@section('content')
    <form method="POST" class="card card-pad narrow"
          action="{{ $course->exists ? route('courses.update', $course) : route('courses.store') }}">
        @csrf
        @if ($course->exists) @method('PUT') @endif

        <div class="field">
            <label for="name">Název</label>
            <input type="text" id="name" name="name" value="{{ old('name', $course->name) }}" required maxlength="255"
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name') <p class="field-error" id="name-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="code">Kód</label>
            <input type="text" id="code" name="code" value="{{ old('code', $course->code) }}" required maxlength="50"
                   @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
            @error('code') <p class="field-error" id="code-error">{{ $message }}</p> @else <p class="hint">Musí být jedinečný.</p> @enderror
        </div>

        <div class="field">
            <label for="capacity">Kapacita</label>
            <input type="number" id="capacity" name="capacity" min="1" step="1" value="{{ old('capacity', $course->capacity) }}" required
                   @error('capacity') aria-invalid="true" aria-describedby="capacity-error" @enderror>
            @error('capacity') <p class="field-error" id="capacity-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="starts_at">Termín</label>
            <input type="datetime-local" id="starts_at" name="starts_at" required
                   value="{{ old('starts_at', $course->starts_at?->format('Y-m-d\TH:i')) }}"
                   @error('starts_at') aria-invalid="true" aria-describedby="starts_at-error" @enderror>
            @error('starts_at') <p class="field-error" id="starts_at-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Uložit</button>
            <a class="btn" href="{{ $course->exists ? route('courses.show', $course) : route('courses.index') }}">Zrušit</a>
        </div>
    </form>
@endsection
