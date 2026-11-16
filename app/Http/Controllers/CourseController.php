<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'free' => ['nullable', 'boolean'],
        ]);

        $courses = Course::withCount(['activeEnrollments as enrolled_count'])
            ->when($filter['from'] ?? null, fn ($q, $from) => $q->whereDate('starts_at', '>=', $from))
            ->when($filter['to'] ?? null, fn ($q, $to) => $q->whereDate('starts_at', '<=', $to))
            ->when($filter['free'] ?? null, fn ($q) => $q->whereRaw(
                'capacity > (select count(*) from enrollments where course_id = courses.id and status = ?)',
                [Enrollment::ENROLLED]
            ))
            ->orderBy('starts_at')
            ->get();

        return view('courses.index', compact('courses', 'filter'));
    }

    public function create()
    {
        return view('courses.form', ['course' => new Course]);
    }

    public function store(Request $request)
    {
        Course::create($this->validated($request, new Course));

        return redirect()->route('courses.index')->with('status', 'Kurz byl vytvořen.');
    }

    public function show(Course $course)
    {
        $course->load('enrollments.participant');
        $enrolledCount = $course->enrollments->where('status', Enrollment::ENROLLED)->count();
        $participants = Participant::orderBy('name')->get();

        return view('courses.show', compact('course', 'enrolledCount', 'participants'));
    }

    public function edit(Course $course)
    {
        return view('courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $course->update($this->validated($request, $course));

        return redirect()->route('courses.show', $course)->with('status', 'Kurz byl upraven.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Kurz byl smazán.');
    }

    private function validated(Request $request, Course $course): array
    {
        // Kapacitu nelze snížit pod počet už aktivních přihlášek.
        $min = max(1, $course->exists ? $course->activeEnrollments()->count() : 1);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('courses')->ignore($course)],
            'capacity' => ['required', 'integer', 'min:'.$min],
            'starts_at' => ['required', 'date'],
        ]);
    }
}
