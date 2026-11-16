<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['course_id', 'participant_id', 'enrolled_at', 'status'])]
class Enrollment extends Model
{
    const ENROLLED = 'enrolled';

    const CANCELLED = 'cancelled';

    const STATUSES = [
        'enrolled' => 'přihlášen',
        'cancelled' => 'zrušeno',
        'completed' => 'dokončeno',
    ];

    protected function casts(): array
    {
        return ['enrolled_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
