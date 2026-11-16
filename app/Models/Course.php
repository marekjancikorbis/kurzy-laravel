<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'capacity', 'starts_at'])]
class Course extends Model
{
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'capacity' => 'integer'];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Aktivní přihlášky = stav enrolled; jen ty zabírají místo v kapacitě. */
    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', Enrollment::ENROLLED);
    }
}
