<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParticipantController extends Controller
{
    public function index()
    {
        $participants = Participant::withCount('enrollments')->orderBy('name')->get();

        return view('participants.index', compact('participants'));
    }

    public function create()
    {
        return view('participants.form', ['participant' => new Participant]);
    }

    public function store(Request $request)
    {
        Participant::create($this->validated($request, new Participant));

        return redirect()->route('participants.index')->with('status', 'Účastník byl vytvořen.');
    }

    public function edit(Participant $participant)
    {
        return view('participants.form', compact('participant'));
    }

    public function update(Request $request, Participant $participant)
    {
        $participant->update($this->validated($request, $participant));

        return redirect()->route('participants.index')->with('status', 'Účastník byl upraven.');
    }

    public function destroy(Participant $participant)
    {
        $participant->delete();

        return redirect()->route('participants.index')->with('status', 'Účastník byl smazán.');
    }

    private function validated(Request $request, Participant $participant): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('participants')->ignore($participant)],
        ]);
    }
}
