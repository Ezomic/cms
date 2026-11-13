<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A copy of a booking made in Chronos, pushed here by its webhook. Chronos
 * stays the source of truth: only read_at is CMS-owned state.
 *
 * @property int $id
 * @property string $uuid
 * @property string $type
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property string $guest_name
 * @property string $guest_email
 * @property string|null $guest_phone
 * @property string|null $guest_note
 * @property string $guest_timezone
 * @property string $locale
 * @property CarbonImmutable $booked_at
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable|null $read_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'uuid',
    'type',
    'starts_at',
    'ends_at',
    'guest_name',
    'guest_email',
    'guest_phone',
    'guest_note',
    'guest_timezone',
    'locale',
    'booked_at',
    'cancelled_at',
])]
class Booking extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'booked_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'read_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
