@extends('layouts.app')
@section('title', $participant->exists ? 'Upravit účastníka' : 'Nový účastník')
@section('inline-errors', '1')

@section('content')
    <form method="POST" class="card card-pad narrow"
          action="{{ $participant->exists ? route('participants.update', $participant) : route('participants.store') }}">
        @csrf
        @if ($participant->exists) @method('PUT') @endif

        <div class="field">
            <label for="name">Jméno</label>
            <input type="text" id="name" name="name" value="{{ old('name', $participant->name) }}" required maxlength="255" autocomplete="name"
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name') <p class="field-error" id="name-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email', $participant->email) }}" required maxlength="255" autocomplete="email"
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @else <p class="hint">Musí být jedinečný.</p> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Uložit</button>
            <a class="btn" href="{{ route('participants.index') }}">Zrušit</a>
        </div>
    </form>
@endsection
