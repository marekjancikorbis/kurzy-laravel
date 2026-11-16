<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $data = $request->validate(['participant_id' => ['required', 'exists:participants,id']]);

        DB::transaction(function () use ($course, $data) {
            $enrollment = Enrollment::firstOrNew(['course_id' => $course->id, 'participant_id' => $data['participant_id']]);

            // Zrušenou přihlášku obnovíme, cokoli jiného by bylo druhé přihlášení.
            if ($enrollment->exists && $enrollment->status !== Enrollment::CANCELLED) {
                throw ValidationException::withMessages(['participant_id' => 'Účastník už je do tohoto kurzu přihlášen.']);
            }

            $this->ensureFreePlace($course, 'participant_id');
            $enrollment->fill(['status' => Enrollment::ENROLLED, 'enrolled_at' => now()])->save();
        });

        return back()->with('status', 'Účastník byl přihlášen.');
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Enrollment::STATUSES))]]);

        DB::transaction(function () use ($enrollment, $data) {
            // Návrat do stavu enrolled znovu zabírá místo.
            if ($data['status'] === Enrollment::ENROLLED && $enrollment->status !== Enrollment::ENROLLED) {
                $this->ensureFreePlace($enrollment->course, 'status');
            }

            $enrollment->update($data);
        });

        return back()->with('status', 'Stav přihlášky byl změněn.');
    }

    private function ensureFreePlace(Course $course, string $field): void
    {
        if ($course->activeEnrollments()->count() >= $course->capacity) {
            throw ValidationException::withMessages([$field => 'Kurz je plně obsazen.']);
        }
    }
}
