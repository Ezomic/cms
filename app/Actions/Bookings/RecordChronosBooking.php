<?php

declare(strict_types=1);

namespace App\Actions\Bookings;

use App\Http\Requests\StoreChronosBookingRequest;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

class RecordChronosBooking
{
    /**
     * @param  array<string, string|null>  $data  the validated "booking" object
     */
    public function handle(string $event, array $data): void
    {
        try {
            $this->record($event, $data);
        } catch (UniqueConstraintViolationException) {
            // Two deliveries for the same new uuid raced and the other one
            // inserted first. The row exists now, so this pass updates it.
            $this->record($event, $data);
        }
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function record(string $event, array $data): void
    {
        $attributes = [...$this->attributes($data), 'cancelled_at' => $this->cancelledAt($event, $data)];
        $booking = Booking::firstWhere('uuid', $attributes['uuid']);

        if ($booking === null) {
            Booking::create($attributes);

            return;
        }

        // Deliveries can arrive out of order. Once cancelled, a late or
        // replayed event must not un-cancel the booking or reset read_at.
        if ($booking->cancelled_at !== null) {
            return;
        }

        // A cancellation makes a read booking unread again, so it is noticed.
        $booking->forceFill([
            ...$attributes,
            'read_at' => $attributes['cancelled_at'] === null ? $booking->read_at : null,
        ])->save();
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function cancelledAt(string $event, array $data): ?CarbonImmutable
    {
        if (isset($data['cancelled_at'])) {
            return $this->time($data['cancelled_at']);
        }

        return $event === StoreChronosBookingRequest::EVENT_CANCELLED ? CarbonImmutable::now() : null;
    }

    /**
     * @param  array<string, string|null>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            // MySQL compares the unique uuid case-insensitively, SQLite does
            // not. One spelling keeps the lookup the same on both.
            'uuid' => strtolower((string) $data['uuid']),
            'type' => $this->text($data['type']),
            'starts_at' => $this->time((string) $data['starts_at']),
            'ends_at' => $this->time((string) $data['ends_at']),
            'guest_name' => $this->text($data['guest_name']),
            'guest_email' => $this->text($data['guest_email']),
            'guest_phone' => $this->text($data['guest_phone'] ?? null),
            'guest_note' => $this->text($data['guest_note'] ?? null),
            'guest_timezone' => $this->text($data['guest_timezone']),
            'locale' => $this->text($data['locale']),
            'booked_at' => $this->time((string) $data['created_at']),
        ];
    }

    private function text(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function time(string $value): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(StoreChronosBookingRequest::TIME_FORMAT, $value, 'UTC')
            ?: throw new \InvalidArgumentException("Unparseable time: {$value}");
    }
}
