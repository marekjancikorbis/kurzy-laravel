<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursesTest extends TestCase
{
    use RefreshDatabase;

    private array $course = ['name' => 'Laravel pro začátečníky', 'code' => 'LAR-1', 'capacity' => 1, 'starts_at' => '2026-12-01T09:00'];

    /** Povinná automatická kontrola: vytvoří kurz a ověří jeho uložení. */
    public function test_course_is_created_and_stored(): void
    {
        $this->post('/courses', $this->course)->assertRedirect('/courses');

        $this->assertDatabaseHas('courses', ['code' => 'LAR-1', 'name' => 'Laravel pro začátečníky', 'capacity' => 1]);
        $this->get('/courses')->assertOk()->assertSee('LAR-1');
    }

    public function test_course_code_is_unique_and_capacity_is_positive_integer(): void
    {
        $this->post('/courses', $this->course);

        $this->post('/courses', $this->course)->assertSessionHasErrors('code');
        foreach ([0, -3, '2.5', 'abc'] as $capacity) {
            $this->post('/courses', ['code' => 'X-'.$capacity, 'capacity' => $capacity] + $this->course)
                ->assertSessionHasErrors('capacity');
        }
        $this->assertDatabaseCount('courses', 1);
    }

    public function test_participant_email_is_valid_and_unique(): void
    {
        $this->post('/participants', ['name' => 'Jan', 'email' => 'jan@example.com'])->assertSessionHasNoErrors();

        $this->post('/participants', ['name' => 'Jan 2', 'email' => 'jan@example.com'])->assertSessionHasErrors('email');
        $this->post('/participants', ['name' => 'Eva', 'email' => 'neni-email'])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('participants', 1);
    }

    public function test_enrollment_rejects_duplicates_and_respects_capacity(): void
    {
        $course = Course::create($this->course); // kapacita 1
        $jan = Participant::create(['name' => 'Jan', 'email' => 'jan@example.com']);
        $eva = Participant::create(['name' => 'Eva', 'email' => 'eva@example.com']);
        $url = "/courses/{$course->id}/enrollments";

        $this->post($url, ['participant_id' => $jan->id])->assertSessionHasNoErrors();
        $this->post($url, ['participant_id' => $jan->id])->assertSessionHasErrors('participant_id'); // dvakrát
        $this->post($url, ['participant_id' => $eva->id])->assertSessionHasErrors('participant_id'); // plno
        $this->assertDatabaseCount('enrollments', 1);

        // Zrušení uvolní místo; návrat do enrolled na plný kurz neprojde.
        $janEnrollment = $course->enrollments()->value('id');
        $this->patch("/enrollments/{$janEnrollment}", ['status' => 'cancelled'])->assertSessionHasNoErrors();
        $this->post($url, ['participant_id' => $eva->id])->assertSessionHasNoErrors();
        $this->patch("/enrollments/{$janEnrollment}", ['status' => 'enrolled'])->assertSessionHasErrors('status');
        $this->patch("/enrollments/{$janEnrollment}", ['status' => 'bogus'])->assertSessionHasErrors('status');

        $evaEnrollment = $course->enrollments()->where('participant_id', $eva->id)->value('id');
        $this->patch("/enrollments/{$evaEnrollment}", ['status' => 'completed'])->assertSessionHasNoErrors();
        $this->assertSame(0, $course->activeEnrollments()->count());

        // Zrušená přihláška se obnoví, nevznikne druhý řádek.
        $this->post($url, ['participant_id' => $jan->id])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('enrollments', 2);
    }

    public function test_update_keeps_uniqueness_and_capacity_rules(): void
    {
        $course = Course::create($this->course);
        $other = Course::create(['code' => 'PHP-2'] + $this->course);
        $jan = Participant::create(['name' => 'Jan', 'email' => 'jan@example.com']);
        $eva = Participant::create(['name' => 'Eva', 'email' => 'eva@example.com']);

        // Vlastní kód / e-mail smí zůstat, cizí ne.
        $this->put("/courses/{$course->id}", ['name' => 'Nový název', 'capacity' => 2] + $this->course)->assertSessionHasNoErrors();
        $this->put("/courses/{$other->id}", $this->course)->assertSessionHasErrors('code');
        $this->put("/participants/{$jan->id}", ['name' => 'Jan Novák', 'email' => 'jan@example.com'])->assertSessionHasNoErrors();
        $this->put("/participants/{$eva->id}", ['name' => 'Eva', 'email' => 'jan@example.com'])->assertSessionHasErrors('email');
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Nový název', 'capacity' => 2]);
        $this->assertDatabaseHas('participants', ['id' => $jan->id, 'name' => 'Jan Novák']);

        // Kapacitu nelze snížit pod počet aktivních přihlášek.
        $this->post("/courses/{$course->id}/enrollments", ['participant_id' => $jan->id]);
        $this->post("/courses/{$course->id}/enrollments", ['participant_id' => $eva->id]);
        $this->put("/courses/{$course->id}", ['capacity' => 1] + $this->course)->assertSessionHasErrors('capacity');
    }

    public function test_deleting_course_or_participant_removes_their_enrollments(): void
    {
        $course = Course::create(['capacity' => 5] + $this->course);
        $other = Course::create(['code' => 'PHP-2'] + $this->course);
        $jan = Participant::create(['name' => 'Jan', 'email' => 'jan@example.com']);
        $this->post("/courses/{$course->id}/enrollments", ['participant_id' => $jan->id]);
        $this->post("/courses/{$other->id}/enrollments", ['participant_id' => $jan->id]);

        $this->delete("/courses/{$course->id}")->assertRedirect('/courses');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseCount('enrollments', 1);

        $this->delete("/participants/{$jan->id}")->assertRedirect('/participants');
        $this->assertDatabaseCount('participants', 0);
        $this->assertDatabaseCount('enrollments', 0);
        $this->get("/courses/{$other->id}")->assertOk();
    }

    public function test_courses_are_filtered_by_date_and_free_places(): void
    {
        $full = Course::create($this->course);
        Course::create(['code' => 'PHP-2', 'name' => 'PHP pokročilí', 'starts_at' => '2027-02-01 09:00'] + $this->course);
        $full->enrollments()->create([
            'participant_id' => Participant::create(['name' => 'Jan', 'email' => 'jan@example.com'])->id,
            'enrolled_at' => now(), 'status' => 'enrolled',
        ]);

        $this->get('/courses?free=1')->assertSee('PHP-2')->assertDontSee('LAR-1');
        $this->get('/courses?from=2027-01-01')->assertSee('PHP-2')->assertDontSee('LAR-1');
        $this->get('/courses?to=2026-12-31')->assertSee('LAR-1')->assertDontSee('PHP-2');
    }
}
