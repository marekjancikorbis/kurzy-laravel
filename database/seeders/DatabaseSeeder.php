<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Ukázková data: php artisan db:seed (na prázdné databázi). */
    public function run(): void
    {
        $people = collect([
            ['Jan Novák', 'jan.novak@example.com'],
            ['Eva Dvořáková', 'eva.dvorakova@example.com'],
            ['Petr Svoboda', 'petr.svoboda@example.com'],
            ['Lucie Černá', 'lucie.cerna@example.com'],
        ])->map(fn ($p) => Participant::create(['name' => $p[0], 'email' => $p[1]]));

        $courses = [
            ['Laravel pro začátečníky', 'LAR-101', 12, '2026-12-01 09:00', ['enrolled', 'enrolled', 'cancelled']],
            ['Databáze a SQL', 'SQL-201', 2, '2026-12-08 13:30', ['enrolled', 'enrolled']],
            ['Základy HTML a CSS', 'WEB-100', 20, '2026-10-05 08:00', ['completed', 'completed', 'completed', 'completed']],
            ['JavaScript v praxi', 'JS-150', 8, '2027-01-12 10:00', []],
        ];

        foreach ($courses as [$name, $code, $capacity, $startsAt, $statuses]) {
            $course = Course::create(['name' => $name, 'code' => $code, 'capacity' => $capacity, 'starts_at' => $startsAt]);

            foreach ($statuses as $i => $status) {
                $course->enrollments()->create(['participant_id' => $people[$i]->id, 'enrolled_at' => now(), 'status' => $status]);
            }
        }
    }
}
